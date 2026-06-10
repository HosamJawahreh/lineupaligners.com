<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientCaseModification;
use App\Rules\Scan3dFile;
use App\Services\CasePhotoStorage;
use App\Support\CaseDataZipStorage;
use App\Support\PhpUploadLimits;
use App\Support\ScanFileStorage;
use App\Services\CaseWorkflowService;
use App\Services\LineUpNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PatientCaseModificationController extends Controller
{
    private const SCAN_MAX_KB = 102400;

    public function __construct(
        protected CaseWorkflowService $workflow
    ) {}

    public function store(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('requestModification', $patient);

        $request->request->remove('stage_number');

        if (PhpUploadLimits::requestPayloadUnparsed($request)) {
            return $this->redirectToTab(
                $patient,
                PhpUploadLimits::uploadTooLargeMessage(),
                'error'
            );
        }

        if (! $patient->canRequestModification()) {
            return $this->redirectToTab(
                $patient,
                'You can request a modification any time after a treatment plan is uploaded and before the case is manufactured, when no modification is already in progress.',
                'error'
            );
        }

        $request->merge([
            'notes' => trim((string) $request->input('notes', '')),
        ]);

        $validated = $request->validate([
            'notes' => ['required', 'string', 'min:1', 'max:10000'],
            'upper_jaw_scan' => ['nullable', 'file', new Scan3dFile(self::SCAN_MAX_KB)],
            'lower_jaw_scan' => ['nullable', 'file', new Scan3dFile(self::SCAN_MAX_KB)],
            'case_data_zip' => ['nullable', 'file', Rule::file()->extensions(['zip'])->max(self::SCAN_MAX_KB)],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:'.CasePhotoStorage::MAX_KB],
        ]);

        $plan = $patient->modificationTargetPlan();

        DB::transaction(function () use ($patient, $request, $validated, $plan) {
            PatientCaseModification::query()
                ->where('patient_id', $patient->id)
                ->where('is_current', true)
                ->whereNull('stage_number')
                ->update(['is_current' => false]);

            $version = (int) PatientCaseModification::query()
                ->where('patient_id', $patient->id)
                ->whereNull('stage_number')
                ->max('version') + 1;

            $modification = PatientCaseModification::create([
                'patient_id' => $patient->id,
                'stage_number' => null,
                'version' => max(1, $version),
                'is_current' => true,
                'notes' => trim((string) ($validated['notes'] ?? '')),
                'requested_by' => auth()->id(),
                'treatment_plan_id' => $plan?->id,
            ]);

            if ($request->hasFile('upper_jaw_scan')) {
                $this->storeModificationScan($modification, 'upper_jaw_scan', $request->file('upper_jaw_scan'));
            }

            if ($request->hasFile('lower_jaw_scan')) {
                $this->storeModificationScan($modification, 'lower_jaw_scan', $request->file('lower_jaw_scan'));
            }

            if ($request->hasFile('case_data_zip')) {
                if (! Schema::hasColumn('patient_case_modifications', 'case_data_zip')) {
                    throw new \RuntimeException('ZIP uploads require a database update. Run php artisan migrate on the server.');
                }

                CaseDataZipStorage::replaceOnModel(
                    $modification,
                    $request->file('case_data_zip'),
                    "patients/{$patient->id}/modifications/{$modification->id}"
                );
            }

            app(CasePhotoStorage::class)->storeFromRequest($request, $patient, $modification);

            $this->workflow->afterModificationRequested($patient->fresh());
        });

        app(LineUpNotifier::class)->modificationRequested($patient, auth()->user());

        return $this->redirectToTab(
            $patient,
            'Modification request submitted. LineUp will upload a revised plan for your review.',
            'success'
        );
    }

    public function downloadScan(Request $request, Patient $patient, PatientCaseModification $modification, string $scan): BinaryFileResponse
    {
        $this->authorize('view', $patient);

        if ($modification->patient_id !== $patient->id) {
            abort(404);
        }

        $field = $scan === 'upper' ? 'upper_jaw_scan' : 'lower_jaw_scan';
        $path = $modification->{$field};

        if (! $path) {
            abort(404);
        }

        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        if (! $disk->exists($path)) {
            abort(404, 'Scan file not found.');
        }

        $nameField = $field === 'upper_jaw_scan' ? 'upper_jaw_scan_name' : 'lower_jaw_scan_name';
        $filename = $modification->{$nameField}
            ? basename($modification->{$nameField})
            : basename($path);

        $absolutePath = $disk->path($path);
        $mime = $this->scanMimeType($path);

        if ($request->boolean('download')) {
            return response()->download($absolutePath, $filename, ['Content-Type' => $mime]);
        }

        return response()->file($absolutePath, ['Content-Type' => $mime]);
    }

    public function downloadCaseDataZip(Request $request, Patient $patient, PatientCaseModification $modification): BinaryFileResponse
    {
        $this->authorize('view', $patient);

        if ($modification->patient_id !== $patient->id || ! $modification->hasCaseDataZip()) {
            abort(404);
        }

        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $absolutePath = $disk->path($modification->case_data_zip);

        return response()->download($absolutePath, $modification->caseDataZipDisplayName(), [
            'Content-Type' => 'application/zip',
        ]);
    }

    protected function storeModificationScan(PatientCaseModification $modification, string $field, UploadedFile $file): void
    {
        $dir = "patients/{$modification->patient_id}/modifications/{$modification->id}";
        $path = ScanFileStorage::store(
            $file,
            $dir,
            $field === 'upper_jaw_scan' ? 'upper' : 'lower'
        );
        $nameField = $field === 'upper_jaw_scan' ? 'upper_jaw_scan_name' : 'lower_jaw_scan_name';

        $modification->update([
            $field => $path,
            $nameField => $file->getClientOriginalName(),
        ]);
    }

    protected function scanMimeType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'zip' => 'application/zip',
            'obj' => 'model/obj',
            'ply' => 'application/octet-stream',
            default => 'model/stl',
        };
    }

    protected function redirectToTab(
        Patient $patient,
        string $message,
        string $type = 'success'
    ): RedirectResponse {
        return redirect()
            ->route('patients.show', $patient)
            ->with($type, $message)
            ->with('open_tab', 'modification');
    }
}
