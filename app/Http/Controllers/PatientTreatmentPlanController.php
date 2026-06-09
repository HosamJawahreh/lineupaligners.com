<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientCaseModification;
use App\Models\PatientTreatmentPlan;
use App\Services\CaseWorkflowService;
use App\Services\LineUpNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PatientTreatmentPlanController extends Controller
{
    public function __construct(
        protected CaseWorkflowService $workflow
    ) {}

    public function storeFull(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('uploadTreatmentPlan', $patient);

        if ($redirect = $this->guardAdminUpload($patient, null)) {
            return $redirect;
        }

        $validated = $request->validate([
            'plan_url' => ['required', 'url', 'max:2048'],
        ]);

        $isRevisionUpload = false;

        DB::transaction(function () use ($patient, $validated, &$isRevisionUpload) {
            $activeModification = $patient->currentModification(null);

            if ($activeModification !== null && $patient->activeRefinementId() === null) {
                $isRevisionUpload = true;
                $this->applyModificationPlanRevision($patient, $activeModification, $validated['plan_url'], null);
                $this->workflow->afterPlanUploaded($patient);

                return;
            }

            $this->createNewTreatmentPlan($patient, $validated['plan_url'], null, null, null, null);
            $this->workflow->afterPlanUploaded($patient);
        });

        $patient->load('doctor.user');
        app(LineUpNotifier::class)->planUploaded($patient, auth()->user(), null, $isRevisionUpload);

        $openTab = ($isRevisionUpload || $patient->activeRefinementId())
            ? 'manufacture-plan'
            : 'modification';

        return $this->redirectToTab(
            $patient,
            'Treatment plan submitted for doctor review.',
            'success',
            null,
            $openTab
        );
    }

    public function storeStage(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('uploadTreatmentPlan', $patient);

        if (! $patient->isDividedStages()) {
            return $this->redirectToTab($patient, 'Stages apply only to divided-stage cases.', 'error');
        }

        $validated = $request->validate([
            'stage_number' => ['required', 'integer', 'min:1', 'max:99'],
            'plan_url' => ['required', 'url', 'max:2048'],
        ]);

        $stageNumber = (int) $validated['stage_number'];

        if ($redirect = $this->guardAdminUpload($patient, $stageNumber)) {
            return $redirect;
        }

        $existing = $patient->currentTreatmentPlanForStage($stageNumber);

        if ($existing === null && ! $patient->canAdminAddNewDividedStageForStage($stageNumber)) {
            $previous = max(1, $stageNumber - 1);

            return $this->redirectToTab(
                $patient,
                "Stage {$stageNumber} cannot be added until stage {$previous} is approved and has no modification in progress.",
                'error',
                $stageNumber
            );
        }

        $isRevisionUpload = false;

        DB::transaction(function () use ($patient, $validated, $stageNumber, &$isRevisionUpload) {
            $activeModification = $patient->currentModification($stageNumber);

            if ($activeModification !== null && $patient->activeRefinementId() === null) {
                $isRevisionUpload = true;
                $this->applyModificationPlanRevision($patient, $activeModification, $validated['plan_url'], $stageNumber);
                $this->workflow->afterPlanUploaded($patient);

                return;
            }

            $this->createNewTreatmentPlan(
                $patient,
                $validated['plan_url'],
                $stageNumber,
                null,
                null,
                $patient->activeRefinementId()
            );
            $this->workflow->afterPlanUploaded($patient);
        });

        $patient->load('doctor.user');
        app(LineUpNotifier::class)->planUploaded($patient, auth()->user(), $stageNumber, $isRevisionUpload);

        $openTab = ($isRevisionUpload || $patient->activeRefinementId())
            ? 'manufacture-plan'
            : 'modification';

        return $this->redirectToTab(
            $patient,
            "Stage {$stageNumber} submitted for doctor review.",
            'success',
            $stageNumber,
            $openTab
        );
    }

    public function review(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('reviewTreatmentPlan', $patient);

        $validated = $request->validate([
            'plan_id' => ['required', 'integer', Rule::exists('patient_treatment_plans', 'id')->where('patient_id', $patient->id)],
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($validated['decision'] === 'rejected' && trim($validated['comment'] ?? '') === '') {
            return $this->redirectToTab(
                $patient,
                'Please add notes when ordering a modification so LineUp can revise the plan.',
                'error',
                null,
                'manufacture-plan'
            );
        }

        $plan = PatientTreatmentPlan::query()
            ->where('patient_id', $patient->id)
            ->where('id', $validated['plan_id'])
            ->where('is_current', true)
            ->firstOrFail();

        if (! $plan->isPending()) {
            return $this->redirectToTab($patient, 'This plan has already been reviewed.', 'error');
        }

        if ($patient->isDividedStages() && $plan->stage_number !== null) {
            if (! $patient->canDoctorReviewStage($plan->stage_number)) {
                return $this->redirectToTab(
                    $patient,
                    'You can approve or reject only the current stage in the sequence. Complete earlier stages first.',
                    'error',
                    $plan->stage_number
                );
            }
        }

        DB::transaction(function () use ($plan, $validated) {
            $plan->update([
                'review_status' => $validated['decision'],
                'review_comment' => $validated['comment'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            $this->workflow->afterPlanReview($plan->patient, $plan->fresh());
        });

        $patient->refresh()->load('doctor.user');
        $notifier = app(LineUpNotifier::class);
        $notifier->planReviewed(
            $patient,
            auth()->user(),
            $validated['decision'],
            $plan->stage_number
        );

        if ($validated['decision'] === 'approved' && $patient->isReadyForManufacturedMark()) {
            $notifier->caseReadyForManufacture($patient);
        }

        $activeStage = $plan->stage_number;

        if ($validated['decision'] === 'approved') {
            $message = $plan->refinement_id
                ? 'Refinement plan approved. This refinement cycle is complete. The patient may order a new refinement when they return.'
                : 'Treatment plan approved. You may request another modification before manufacture, or wait for LineUp to mark the case as manufactured.';
            $tab = 'manufacture-plan';
        } else {
            $message = 'Modification ordered. LineUp will prepare a revised plan from your notes. Add scans on the Modification tab if needed.';
            $tab = 'modification';
        }

        return $this->redirectToTab($patient, $message, 'success', $activeStage, $tab);
    }

    protected function applyModificationPlanRevision(
        Patient $patient,
        PatientCaseModification $modification,
        string $planUrl,
        ?int $stageNumber
    ): void {
        $plan = $modification->treatmentPlan;

        if ($plan === null || ! $plan->is_current) {
            $plan = $stageNumber === null
                ? $patient->currentFullTreatmentPlan()
                : $patient->currentTreatmentPlanForStage($stageNumber);
        }

        if ($plan === null) {
            throw new \RuntimeException('No treatment plan found to revise for this modification.');
        }

        $plan->update([
            'plan_url' => $planUrl,
            'review_status' => PatientTreatmentPlan::STATUS_PENDING,
            'review_comment' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'uploaded_by' => auth()->id(),
        ]);

        $modification->update(['revised_plan_url' => $planUrl]);
    }

    protected function createNewTreatmentPlan(
        Patient $patient,
        string $planUrl,
        ?int $stageNumber,
        ?int $stepFrom,
        ?int $stepTo,
        ?int $refinementId
    ): PatientTreatmentPlan {
        $scopeQuery = PatientTreatmentPlan::query()
            ->where('patient_id', $patient->id)
            ->where('is_current', true);

        if ($stageNumber !== null) {
            $scopeQuery->where('stage_number', $stageNumber);
        } else {
            $scopeQuery->whereNull('stage_number');
        }

        if ($refinementId) {
            $scopeQuery->where('refinement_id', $refinementId);
        } else {
            $scopeQuery->whereNull('refinement_id');
        }

        $scopeQuery->update(['is_current' => false]);

        $versionQuery = PatientTreatmentPlan::query()
            ->where('patient_id', $patient->id);

        if ($stageNumber !== null) {
            $versionQuery->where('stage_number', $stageNumber);
        } else {
            $versionQuery->whereNull('stage_number');
        }

        if ($refinementId) {
            $versionQuery->where('refinement_id', $refinementId);
        } else {
            $versionQuery->whereNull('refinement_id');
        }

        $version = (int) $versionQuery->max('version') + 1;

        return PatientTreatmentPlan::create([
            'patient_id' => $patient->id,
            'refinement_id' => $refinementId,
            'stage_number' => $stageNumber,
            'step_from' => $stepFrom,
            'step_to' => $stepTo,
            'plan_url' => $planUrl,
            'review_status' => PatientTreatmentPlan::STATUS_PENDING,
            'review_comment' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'uploaded_by' => auth()->id(),
            'version' => max(1, $version),
            'is_current' => true,
        ]);
    }

    protected function guardAdminUpload(Patient $patient, ?int $stageNumber): ?RedirectResponse
    {
        if ($patient->hasActiveModificationFor($stageNumber)) {
            return null;
        }

        $query = $patient->treatmentPlans()->where('is_current', true);

        if ($patient->activeRefinementId()) {
            $query->where('refinement_id', $patient->activeRefinementId());
        } else {
            $query->whereNull('refinement_id');
        }

        if ($stageNumber !== null) {
            $query->where('stage_number', $stageNumber);
        } else {
            $query->whereNull('stage_number');
        }

        $current = $query->first();

        if ($current === null) {
            return null;
        }

        if ($current->isPending()) {
            return $this->redirectToTab(
                $patient,
                'A plan is awaiting doctor review. You can upload a new link only after it is rejected.',
                'error',
                $stageNumber
            );
        }

        if ($current->isApproved()) {
            if ($patient->hasActiveModificationFor($stageNumber) || $patient->hasActiveRefinement()) {
                return null;
            }

            return $this->redirectToTab(
                $patient,
                'This plan is already approved. The doctor must request a modification or refinement before you can upload a revised plan.',
                'error',
                $stageNumber
            );
        }

        return null;
    }

    protected function redirectToTab(
        Patient $patient,
        string $message,
        string $type = 'success',
        ?int $activeStage = null,
        string $tab = 'manufacture-plan'
    ): RedirectResponse {
        $redirect = redirect()
            ->route('patients.show', $patient)
            ->with($type, $message)
            ->with('open_tab', $tab);

        if ($activeStage !== null) {
            $redirect->with('mfg_active_stage', $activeStage);
        }

        return $redirect;
    }
}
