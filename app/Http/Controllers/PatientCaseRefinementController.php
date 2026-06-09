<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientCaseRefinement;
use App\Rules\Scan3dFile;
use App\Services\CasePhotoStorage;
use App\Services\CaseWorkflowService;
use App\Services\LineUpNotifier;
use App\Support\PhpUploadLimits;
use App\Support\ScanZipExtractor;
use App\Support\ScanFileStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PatientCaseRefinementController extends Controller
{
    private const SCAN_MAX_KB = 102400;

    public function __construct(
        protected CaseWorkflowService $workflow
    ) {}

    public function store(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('requestRefinement', $patient);

        if (! Schema::hasTable('patient_case_refinements')) {
            return $this->redirectToTab(
                $patient,
                'Refinement is not available: run php artisan migrate to create patient_case_refinements.',
                'error'
            );
        }

        ScanZipExtractor::normalizeRequestFiles($request, ['upper_jaw_scan', 'lower_jaw_scan']);

        if (PhpUploadLimits::requestPayloadUnparsed($request)) {
            return $this->redirectToTab(
                $patient,
                PhpUploadLimits::uploadTooLargeMessage(),
                'error'
            );
        }

        if (! $patient->canRequestRefinement()) {
            return $this->redirectToTab(
                $patient,
                $patient->hasActiveRefinement()
                    ? 'A refinement is already in progress for this case.'
                    : 'Refinement is available only after LineUp marks the case as Manufactured, with no modification in progress.',
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
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:'.CasePhotoStorage::MAX_KB],
        ]);

        $anchorPlan = $patient->originalCycleFullTreatmentPlan();
        $storedPhotoCount = 0;

        try {
            $refinement = DB::transaction(function () use ($patient, $request, $validated, $anchorPlan, &$storedPhotoCount) {
                PatientCaseRefinement::query()
                    ->where('patient_id', $patient->id)
                    ->where('is_current', true)
                    ->update(['is_current' => false]);

                $maxVersion = PatientCaseRefinement::query()
                    ->where('patient_id', $patient->id)
                    ->max('version');

                $refinement = PatientCaseRefinement::create([
                    'patient_id' => $patient->id,
                    'version' => max(1, (int) $maxVersion + 1),
                    'is_current' => true,
                    'notes' => trim((string) ($validated['notes'] ?? '')),
                    'requested_by' => auth()->id(),
                    'treatment_plan_id' => $anchorPlan?->id,
                ]);

                $this->attachScanIfPresent($request, 'upper_jaw_scan', $refinement, 'upper_jaw_scan');
                $this->attachScanIfPresent($request, 'lower_jaw_scan', $refinement, 'lower_jaw_scan');
                $storedPhotoCount = app(CasePhotoStorage::class)->storeFromRequest($request, $patient, null, $refinement);

                $this->workflow->afterRefinementRequested($patient->fresh());

                return $refinement;
            });
        } catch (\Throwable $e) {
            report($e);

            return $this->redirectToTab(
                $patient,
                'Could not start refinement: '.$e->getMessage(),
                'error'
            );
        }

        $patient->refresh()->load(['doctor.user', 'caseRefinements']);

        if (! $patient->hasActiveRefinement()) {
            return $this->redirectToTab(
                $patient,
                'Refinement could not be saved. Contact support or run php artisan migrate.',
                'error'
            );
        }

        app(LineUpNotifier::class)->refinementRequested($patient, auth()->user());

        $hasAttachments = $storedPhotoCount > 0
            || $request->hasFile('upper_jaw_scan')
            || $request->hasFile('lower_jaw_scan');

        return $this->redirectToTab(
            $patient,
            'Refinement #'.$patient->currentRefinement()?->version.' started. LineUp will upload the new plan on Treatment Plan.',
            'success',
            $hasAttachments ? 'view-data' : 'order-refinement'
        );
    }

    public function downloadScan(Request $request, Patient $patient, PatientCaseRefinement $refinement, string $scan): BinaryFileResponse
    {
        $this->authorize('view', $patient);

        if ($refinement->patient_id !== $patient->id) {
            abort(404);
        }

        $field = $scan === 'upper' ? 'upper_jaw_scan' : 'lower_jaw_scan';
        $path = $refinement->{$field};

        if (! $path) {
            abort(404);
        }

        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        if (! $disk->exists($path)) {
            abort(404, 'Scan file not found.');
        }

        $nameField = $field === 'upper_jaw_scan' ? 'upper_jaw_scan_name' : 'lower_jaw_scan_name';
        $filename = $refinement->{$nameField}
            ? basename($refinement->{$nameField})
            : basename($path);

        $absolutePath = $disk->path($path);
        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'obj' => 'model/obj',
            'ply' => 'application/octet-stream',
            default => 'model/stl',
        };

        if ($request->boolean('download')) {
            return response()->download($absolutePath, $filename, ['Content-Type' => $mime]);
        }

        return response()->file($absolutePath, ['Content-Type' => $mime]);
    }

    protected function attachScanIfPresent(
        Request $request,
        string $inputName,
        PatientCaseRefinement $refinement,
        string $field,
    ): void {
        if (! $request->hasFile($inputName)) {
            return;
        }

        $file = $request->file($inputName);
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            throw new \RuntimeException('The uploaded file "'.$inputName.'" is invalid or incomplete.');
        }

        $dir = "patients/{$refinement->patient_id}/refinements/{$refinement->id}";
        $path = ScanFileStorage::store($file, $dir, $field === 'upper_jaw_scan' ? 'upper' : 'lower');
        $nameField = $field === 'upper_jaw_scan' ? 'upper_jaw_scan_name' : 'lower_jaw_scan_name';

        $refinement->update([
            $field => $path,
            $nameField => $file->getClientOriginalName(),
        ]);
    }

    protected function redirectToTab(
        Patient $patient,
        string $message,
        string $type = 'success',
        string $openTab = 'order-refinement'
    ): RedirectResponse {
        return redirect()
            ->route('patients.show', [
                'patient' => $patient,
                'tab' => $openTab,
            ])
            ->with($type, $message)
            ->with('open_tab', $openTab);
    }
}
