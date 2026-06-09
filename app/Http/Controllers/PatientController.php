<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientCaseMessage;
use App\Services\CaseChatContacts;
use App\Services\CasePhotoStorage;
use App\Services\CaseTimelineBuilder;
use App\Services\LineUpNotifier;
use App\Services\PatientCaseUpdateMailer;
use App\Support\PhpUploadLimits;
use App\Support\ScanZipExtractor;
use App\Models\User;
use App\Models\PatientPhoto;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PatientController extends Controller
{
    private const SCAN_EXTENSIONS = ['stl', 'obj', 'ply'];

    private const SCAN_MAX_KB = 102400;

    private const PHOTO_MAX_KB = 102400;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Patient::class);

        $filters = $this->listFilters($request);
        $patients = $this->filteredPatientsQuery($filters)
            ->with(['doctor', 'photos'])
            ->get();

        return view('theme.pages.patients', [
            'patients' => $patients,
            'filters' => $filters,
            'statusTabs' => config('patient-statuses.tabs', []),
            'caseTypes' => config('patient-case-types', []),
            'clinicName' => $this->clinicNameForList(),
            'isAdmin' => auth()->user()->isAdmin(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Patient::class);

        $filters = $this->listFilters($request);
        $rows = $this->filteredPatientsQuery($filters)
            ->with('doctor')
            ->latest('created_at')
            ->get();

        $filename = 'lineup-cases-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Case Number', 'Doctor', 'Company', 'Case Type', 'Patient Name',
                'Phone', 'Added At', 'Email', 'Workflow',
            ]);

            foreach ($rows as $patient) {
                fputcsv($out, [
                    $patient->display_patient_id,
                    $patient->doctor?->fullName() ?? '',
                    $patient->doctor?->clinicNameForDisplay() ?? Setting::get('clinic_name', ''),
                    $patient->caseTypeLabel(),
                    $patient->fullName(),
                    $patient->phone ?? '',
                    $patient->created_at?->format('Y-m-d H:i'),
                    $patient->email ?? '',
                    $patient->workflowStageLabel(),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function create(): View
    {
        $this->authorize('create', Patient::class);

        $doctors = auth()->user()->isAdmin()
            ? Doctor::where('is_active', true)->orderBy('first_name')->get()
            : collect();

        return view('theme.pages.add-patient', compact('doctors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Patient::class);

        $this->normalizeScanUploads($request);
        $validated = $this->validatePatient($request);
        $this->assertScanRequirement($request);
        $data = $this->patientFields($validated);
        $data['patient_id'] = Patient::generatePatientId();
        $data['status'] = Patient::STATUS_ACTIVE;
        if (Schema::hasColumn('patients', 'case_workflow_stage')) {
            $data['case_workflow_stage'] = config('patient-case-workflow.default_stage', 'created');
        }
        $data['doctor_id'] = $this->resolveDoctorId($request, $validated);

        if (auth()->user()->isDoctor() && ! $data['doctor_id']) {
            abort(403, 'Doctor profile is not linked to your account.');
        }

        $patient = Patient::create($data);
        $this->storeCasePhotos($request, $patient);
        $this->storeJawScans($request, $patient);
        $this->storeCaseDataZip($request, $patient);
        $this->syncPrimaryPhoto($patient);

        $patient->load('doctor.user');
        app(LineUpNotifier::class)->caseCreated($patient, auth()->user());

        return redirect()->route('patients.index')->with('success', 'Patient case study created successfully.');
    }

    public function show(Patient $patient): View
    {
        $this->authorize('view', $patient);

        $relations = ['doctor', 'photos'];
        if (Schema::hasTable('patient_case_messages')) {
            $relations[] = 'caseMessages.user.doctor';
        }
        if (Schema::hasTable('patient_case_modifications')) {
            $relations[] = 'caseModifications.requester';
            $relations[] = 'caseModifications.photos';
        }
        if (Schema::hasTable('patient_case_refinements')) {
            $relations[] = 'caseRefinements.requester';
            $relations[] = 'caseRefinements.photos';
            $relations[] = 'caseRefinements.treatmentPlans';
        }
        $relations[] = 'treatmentPlans.uploader';
        $relations[] = 'treatmentPlans.reviewer';
        $patient->load($relations);

        if (auth()->user()->can('chat', $patient)) {
            PatientCaseMessage::markIncomingAsReadFor($patient, (int) auth()->id());
        }

        $logoUrl = Setting::logoUrl();
        $chatContacts = app(CaseChatContacts::class);

        return view('theme.pages.patient-case-study', [
            'patient' => $patient,
            'workflowSteps' => $patient->workflowProgress(),
            'studyTabs' => config('patient-case-study.tabs', []),
            'clinicName' => $this->clinicNameForPatient($patient),
            'logoUrl' => $logoUrl,
            'canCaseChat' => auth()->user()->can('chat', $patient),
            'chatDoctorName' => $chatContacts->assignedDoctorChatLabel($patient),
            'chatCounterparty' => $chatContacts->counterpartyFor(auth()->user(), $patient, $logoUrl),
            'chatParticipants' => $chatContacts->participants($patient, $logoUrl),
            'latestSeenOwnMessageId' => Schema::hasColumn('patient_case_messages', 'read_at')
                ? (int) ($patient->caseMessages()
                    ->where('user_id', auth()->id())
                    ->whereNotNull('read_at')
                    ->max('id') ?: 0)
                : 0,
            'caseScanSets' => $caseScanSets = $patient->caseScanSetsForViewer(),
            'defaultScanSetKey' => $defaultScanSetKey = $patient->defaultScanSetKey(),
            'caseScanFiles' => collect($caseScanSets)->firstWhere('key', $defaultScanSetKey)['files'] ?? [],
            'casePhotosBySet' => $patient->casePhotosGalleryBySet(),
            'casePhotosOriginal' => $patient->photosForSetKey('original')
                ->map(fn (PatientPhoto $photo) => [
                    'id' => $photo->id,
                    'url' => $photo->url(),
                    'name' => $photo->original_name ?: basename($photo->path),
                    'download_url' => route('patients.photos.download', [$patient, $photo]),
                ])
                ->values()
                ->all(),
            'modificationRecords' => $patient->modificationRecords(),
            'refinementRecords' => $patient->refinementRecords(),
            'caseTimeline' => app(CaseTimelineBuilder::class)->build($patient),
            'modificationTimeline' => app(CaseTimelineBuilder::class)->buildModificationHistory($patient),
            'refinementTimeline' => app(CaseTimelineBuilder::class)->buildRefinementHistory($patient),
            'canRequestModification' => auth()->user()->can('requestModification', $patient),
            'canRequestRefinement' => auth()->user()->can('requestRefinement', $patient),
            'refinementsEnabled' => Schema::hasTable('patient_case_refinements'),
            'activeRefinement' => Schema::hasTable('patient_case_refinements')
                ? $patient->currentRefinement()
                : null,
            'scanUploadLimitsOk' => PhpUploadLimits::isAdequateForScans(),
            'scanUploadLimitsLabel' => PhpUploadLimits::humanSummary(),
            'canUploadTreatmentPlan' => auth()->user()->can('uploadTreatmentPlan', $patient),
            'canReviewTreatmentPlan' => auth()->user()->can('reviewTreatmentPlan', $patient),
            'canMarkManufactured' => auth()->user()->can('markAsManufactured', $patient)
                && $patient->isReadyForManufacturedMark(),
            'treatmentPlanContexts' => $patient->treatmentPlanContextsForViewer(),
            'defaultTreatmentPlanContextKey' => $patient->defaultTreatmentPlanContextKey(),
            'canAdminUploadFullPlan' => $patient->canAdminUploadFullTreatmentPlan(),
            'canSendCaseUpdate' => filled($patient->email),
        ]);
    }

    public function sendLastUpdate(Patient $patient, PatientCaseUpdateMailer $mailer): RedirectResponse
    {
        $this->authorize('view', $patient);

        if (! filled($patient->email)) {
            return redirect()
                ->route('patients.show', $patient)
                ->with('error', 'This patient has no email address on file.');
        }

        try {
            $mailer->send($patient, auth()->user());
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('patients.show', $patient)
                ->with('error', $e->getMessage());
        }

        $latestTitle = $mailer->latestEvent($patient)['title'] ?? 'latest update';

        return redirect()
            ->route('patients.show', $patient)
            ->with('patient_email_sent', $patient->email)
            ->with('success', 'Email sent successfully to '.$patient->email.' — '.$latestTitle.'.');
    }

    public function downloadScan(Request $request, Patient $patient, string $scan): BinaryFileResponse
    {
        $this->authorize('view', $patient);

        $field = $scan === 'upper' ? 'upper_jaw_scan' : 'lower_jaw_scan';
        $path = $patient->{$field};

        if (! $path) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            abort(404, 'Scan file not found. It may have been removed or storage is not linked.');
        }

        $filename = $patient->scanDownloadFilename($field);
        $absolutePath = $disk->path($path);
        $mime = match ($patient->scanExtension($field)) {
            'obj' => 'model/obj',
            'ply' => 'application/octet-stream',
            default => 'model/stl',
        };

        if ($request->boolean('download')) {
            return response()->download($absolutePath, $filename, [
                'Content-Type' => $mime,
            ]);
        }

        return response()->file($absolutePath, [
            'Content-Type' => $mime,
        ]);
    }

    public function downloadCaseDataZip(Patient $patient): BinaryFileResponse
    {
        $this->authorize('view', $patient);

        if (! $patient->hasCaseDataZip()) {
            abort(404, 'Case data archive not found.');
        }

        $disk = Storage::disk('public');
        $absolutePath = $disk->path($patient->case_data_zip);

        return response()->download($absolutePath, $patient->caseDataZipDisplayName(), [
            'Content-Type' => 'application/zip',
        ]);
    }

    public function downloadPhoto(Patient $patient, PatientPhoto $photo): BinaryFileResponse
    {
        $this->authorize('view', $patient);

        if ($photo->patient_id !== $patient->id) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($photo->path)) {
            abort(404, 'Photo file not found.');
        }

        return response()->download($disk->path($photo->path), $photo->downloadFilename());
    }

    public function downloadAllPhotos(Request $request, Patient $patient): BinaryFileResponse
    {
        $this->authorize('view', $patient);

        $setKey = $request->query('set', 'original');
        $photos = $patient->photosForSetKey($setKey);

        if ($photos->isEmpty()) {
            abort(404);
        }

        $disk = Storage::disk('public');
        $zipPath = tempnam(sys_get_temp_dir(), 'case_photos_');
        $zip = new \ZipArchive;

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create download archive.');
        }

        $usedNames = [];

        foreach ($photos as $photo) {
            if (! $disk->exists($photo->path)) {
                continue;
            }

            $name = $photo->downloadFilename();
            if (isset($usedNames[$name])) {
                $usedNames[$name]++;
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $base = pathinfo($name, PATHINFO_FILENAME);
                $name = $base.'-'.$usedNames[$name].($ext ? '.'.$ext : '');
            } else {
                $usedNames[$name] = 1;
            }

            $zip->addFile($disk->path($photo->path), $name);
        }

        if ($zip->numFiles === 0) {
            $zip->close();
            @unlink($zipPath);

            abort(404, 'No photo files available to download.');
        }

        $zip->close();

        $suffix = $setKey === 'original' ? 'original' : $setKey;
        $filename = $patient->display_patient_id.'-'.$suffix.'-photos.zip';

        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }

    public function edit(Patient $patient): View
    {
        $this->authorize('update', $patient);

        $patient->load('photos');
        $doctors = auth()->user()->isAdmin()
            ? Doctor::where('is_active', true)->orderBy('first_name')->get()
            : collect();

        return view('theme.pages.add-patient', compact('patient', 'doctors'));
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('update', $patient);

        $this->normalizeScanUploads($request);
        $validated = $this->validatePatient($request, $patient);
        $this->assertScanRequirement($request, $patient);
        $data = $this->patientFields($validated);
        $data['status'] = Patient::STATUS_ACTIVE;
        $data['doctor_id'] = $this->resolveDoctorId($request, $validated, $patient);

        $patient->update($data);
        $this->removeCasePhotos($request, $patient);
        $this->storeCasePhotos($request, $patient);
        $this->storeJawScans($request, $patient);
        $this->storeCaseDataZip($request, $patient);
        $this->syncPrimaryPhoto($patient);

        return redirect()->route('patients.show', $patient)->with('success', 'Patient case study updated successfully.');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $this->authorize('delete', $patient);

        $patient->delete();

        return redirect()->route('patients.index')->with('success', 'Patient removed successfully.');
    }

    private function normalizeScanUploads(Request $request): void
    {
        ScanZipExtractor::normalizeRequestFiles($request, ['upper_jaw_scan', 'lower_jaw_scan']);
    }

    private function validatePatient(Request $request, ?Patient $patient = null): array
    {
        $caseTypes = array_keys(config('patient-case-types', []));

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'case_type' => ['required', 'string', Rule::in($caseTypes)],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female,other'],
            'notes' => ['nullable', 'string'],
            'upper_jaw_scan' => ['nullable', 'file', Rule::file()->extensions(self::SCAN_EXTENSIONS)->max(self::SCAN_MAX_KB)],
            'lower_jaw_scan' => ['nullable', 'file', Rule::file()->extensions(self::SCAN_EXTENSIONS)->max(self::SCAN_MAX_KB)],
            'remove_upper_jaw_scan' => ['sometimes', 'boolean'],
            'remove_lower_jaw_scan' => ['sometimes', 'boolean'],
            'case_data_zip' => ['nullable', 'file', 'mimes:zip', 'max:'.self::SCAN_MAX_KB],
            'remove_case_data_zip' => ['sometimes', 'boolean'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:'.self::PHOTO_MAX_KB],
            'remove_photos' => ['nullable', 'array'],
        ];

        if ($patient) {
            $rules['remove_photos.*'] = [
                'integer',
                Rule::exists('patient_photos', 'id')->where('patient_id', $patient->id),
            ];
        }

        if (auth()->user()->isAdmin()) {
            $rules['doctor_id'] = ['required', 'exists:doctors,id'];
        }

        return $request->validate($rules);
    }

    private function assertScanRequirement(Request $request, ?Patient $patient = null): void
    {
        $requirement = Setting::scanRequirement();

        if ($requirement === 'optional') {
            return;
        }

        $hasUpper = $request->hasFile('upper_jaw_scan')
            || ($patient && $patient->upper_jaw_scan && ! $request->boolean('remove_upper_jaw_scan'));
        $hasLower = $request->hasFile('lower_jaw_scan')
            || ($patient && $patient->lower_jaw_scan && ! $request->boolean('remove_lower_jaw_scan'));

        if ($requirement === 'both' && (! $hasUpper || ! $hasLower)) {
            throw ValidationException::withMessages([
                'upper_jaw_scan' => 'Both upper and lower jaw scans are required for new cases.',
            ]);
        }

        if ($requirement === 'at_least_one' && ! $hasUpper && ! $hasLower) {
            throw ValidationException::withMessages([
                'upper_jaw_scan' => 'Upload at least one jaw scan (upper or lower).',
            ]);
        }
    }

    private function patientFields(array $validated): array
    {
        $name = Patient::splitName($validated['name']);
        $dob = $validated['date_of_birth'] ?? null;

        return [
            'first_name' => $name['first_name'],
            'last_name' => $name['last_name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'date_of_birth' => $dob,
            'age' => $dob ? (int) Carbon::parse($dob)->age : null,
            'gender' => $validated['gender'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'case_type' => $validated['case_type'],
            'doctor_id' => $validated['doctor_id'] ?? null,
        ];
    }

    private function resolveDoctorId(Request $request, array $validated, ?Patient $patient = null): ?int
    {
        if (auth()->user()->isDoctor()) {
            return auth()->user()->doctor?->id;
        }

        return $request->integer('doctor_id') ?: ($patient?->doctor_id);
    }

    private function storeCasePhotos(Request $request, Patient $patient): void
    {
        app(CasePhotoStorage::class)->storeFromRequest($request, $patient);
    }

    private function removeCasePhotos(Request $request, Patient $patient): void
    {
        $ids = $request->input('remove_photos', []);

        if (empty($ids)) {
            return;
        }

        $photos = $patient->photos()->whereIn('id', $ids)->get();

        foreach ($photos as $photo) {
            if (Storage::disk('public')->exists($photo->path)) {
                Storage::disk('public')->delete($photo->path);
            }
            $photo->delete();
        }
    }

    private function syncPrimaryPhoto(Patient $patient): void
    {
        app(CasePhotoStorage::class)->syncPrimaryPhoto($patient);
    }

    private function storeJawScans(Request $request, Patient $patient): void
    {
        if ($request->hasFile('upper_jaw_scan')) {
            $this->replaceScan($patient, 'upper_jaw_scan', $request->file('upper_jaw_scan'));
        } elseif ($request->boolean('remove_upper_jaw_scan')) {
            $this->removeScan($patient, 'upper_jaw_scan');
        }

        if ($request->hasFile('lower_jaw_scan')) {
            $this->replaceScan($patient, 'lower_jaw_scan', $request->file('lower_jaw_scan'));
        } else        if ($request->boolean('remove_lower_jaw_scan')) {
            $this->removeScan($patient, 'lower_jaw_scan');
        }
    }

    private function storeCaseDataZip(Request $request, Patient $patient): void
    {
        if ($request->hasFile('case_data_zip')) {
            $this->replaceCaseDataZip($patient, $request->file('case_data_zip'));
        } elseif ($request->boolean('remove_case_data_zip')) {
            $this->removeCaseDataZip($patient);
        }
    }

    private function replaceCaseDataZip(Patient $patient, UploadedFile $file): void
    {
        $oldPath = $patient->case_data_zip;
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'case-data';
        $filename = $base.'.zip';
        $dir = "patients/{$patient->id}";

        if (Storage::disk('public')->exists("{$dir}/{$filename}")) {
            $filename = $base.'_'.time().'.zip';
        }

        $path = $file->storeAs($dir, $filename, 'public');

        $patient->update([
            'case_data_zip' => $path,
            'case_data_zip_name' => $file->getClientOriginalName(),
        ]);
    }

    private function removeCaseDataZip(Patient $patient): void
    {
        $path = $patient->case_data_zip;
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $patient->update([
            'case_data_zip' => null,
            'case_data_zip_name' => null,
        ]);
    }

    private function replaceScan(Patient $patient, string $field, UploadedFile $file): void
    {
        $oldPath = $patient->{$field};
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'stl');
        if (! in_array($ext, self::SCAN_EXTENSIONS, true)) {
            $ext = 'stl';
        }

        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            ?: ($field === 'upper_jaw_scan' ? 'upper' : 'lower');
        $filename = $base.'.'.$ext;
        $dir = "patients/{$patient->id}/scans";

        if (Storage::disk('public')->exists("{$dir}/{$filename}")) {
            $filename = $base.'_'.time().'.'.$ext;
        }

        $path = $file->storeAs($dir, $filename, 'public');
        $nameField = $field === 'upper_jaw_scan' ? 'upper_jaw_scan_name' : 'lower_jaw_scan_name';

        $patient->update([
            $field => $path,
            $nameField => $file->getClientOriginalName(),
        ]);
    }

    private function removeScan(Patient $patient, string $field): void
    {
        $path = $patient->{$field};
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $nameField = $field === 'upper_jaw_scan' ? 'upper_jaw_scan_name' : 'lower_jaw_scan_name';

        $patient->update([
            $field => null,
            $nameField => null,
        ]);
    }

    private function scopedPatientsQuery()
    {
        $query = Patient::query();

        if (auth()->user()->isDoctor()) {
            $query->where('doctor_id', auth()->user()->doctor?->id);
        }

        return $query;
    }

    private function listFilters(Request $request): array
    {
        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 20;
        }

        return [
            'status' => $this->filterStatus($request),
            'patient' => $this->filterString($request, 'patient'),
            'doctor' => $this->filterString($request, 'doctor'),
            'creator' => $this->filterString($request, 'creator'),
            'case_type' => $this->filterString($request, 'case_type'),
            'date_from' => $this->filterString($request, 'date_from'),
            'date_to' => $this->filterString($request, 'date_to'),
            'sort' => $request->input('sort', 'created_at'),
            'dir' => $request->input('dir', 'desc') === 'asc' ? 'asc' : 'desc',
            'per_page' => $perPage,
        ];
    }

    private function filterString(Request $request, string $key): string
    {
        $value = $request->input($key);

        if ($value === null || $value === '') {
            return '';
        }

        return trim((string) $value);
    }

    private function filterStatus(Request $request): string
    {
        $status = $this->filterString($request, 'status');
        $allowed = array_keys(config('patient-statuses.tabs', []));

        if ($status !== '' && ! in_array($status, $allowed, true)) {
            return '';
        }

        return $status;
    }

    private function isValidDateFilter(string $value): bool
    {
        return $value !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }

    private function filteredPatientsQuery(array $filters)
    {
        $query = $this->scopedPatientsQuery();

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if ($filters['patient'] !== '') {
            $this->applyPersonNameFilter($query, $filters['patient'], [
                'first_name',
                'last_name',
                'email',
                'phone',
                'patient_id',
            ], fullNameColumns: ['first_name', 'last_name']);
        }

        if ($filters['doctor'] !== '' && auth()->user()->isAdmin()) {
            $query->whereHas('doctor', function ($q) use ($filters) {
                $this->applyPersonNameFilter($q, $filters['doctor'], [
                    'first_name',
                    'last_name',
                    'email',
                ], fullNameColumns: ['first_name', 'last_name']);
            });
        }

        if ($filters['creator'] !== '' && auth()->user()->isAdmin()) {
            $query->whereHas('doctor', function ($q) use ($filters) {
                $q->whereHas('user', function ($userQuery) use ($filters) {
                    $this->applyPersonNameFilter($userQuery, $filters['creator'], [
                        'name',
                        'email',
                    ]);
                });
            });
        }

        if ($filters['case_type'] !== '' && array_key_exists($filters['case_type'], config('patient-case-types', []))) {
            $query->where('case_type', $filters['case_type']);
        }

        if ($filters['date_from'] !== '' && $this->isValidDateFilter($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '' && $this->isValidDateFilter($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $sortColumn = in_array($filters['sort'], ['created_at', 'patient_id'], true)
            ? $filters['sort']
            : 'created_at';

        return $query->orderBy($sortColumn, $filters['dir']);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  list<string>  $columns
     * @param  list<string>|null  $fullNameColumns
     */
    private function applyPersonNameFilter($query, string $term, array $columns, ?array $fullNameColumns = null): void
    {
        $needle = '%'.trim($term).'%';
        $words = array_values(array_filter(preg_split('/\s+/', trim($term)) ?: []));

        $query->where(function ($q) use ($needle, $words, $columns, $fullNameColumns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', $needle);
            }

            if ($fullNameColumns !== null && count($fullNameColumns) === 2) {
                [$first, $last] = $fullNameColumns;
                $q->orWhereRaw("CONCAT({$first}, ' ', {$last}) LIKE ?", [$needle])
                    ->orWhereRaw("CONCAT({$last}, ' ', {$first}) LIKE ?", [$needle]);
            }

            if (count($words) >= 2 && $fullNameColumns !== null && count($fullNameColumns) === 2) {
                [$first, $last] = $fullNameColumns;
                $q->orWhere(function ($sub) use ($words, $first, $last) {
                    $sub->where($first, 'like', '%'.$words[0].'%')
                        ->where($last, 'like', '%'.$words[1].'%');
                });
            }
        });
    }

    protected function clinicNameForList(): string
    {
        $user = auth()->user();

        if ($user->isDoctor() && $user->doctor) {
            return $user->doctor->clinicNameForDisplay();
        }

        return Setting::get('clinic_name', config('app.name'));
    }

    protected function clinicNameForPatient(Patient $patient): string
    {
        return $patient->doctor?->clinicNameForDisplay()
            ?? Setting::get('clinic_name', config('app.name'));
    }
}
