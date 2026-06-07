<?php

namespace App\Http\Controllers;

use App\Models\Patient;
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

        DB::transaction(function () use ($patient, $validated) {
            $activeModification = $patient->currentModification(null);
            $refinementId = $patient->activeRefinementId();

            $scopeQuery = PatientTreatmentPlan::query()
                ->where('patient_id', $patient->id)
                ->whereNull('stage_number')
                ->where('is_current', true);

            if ($refinementId) {
                $scopeQuery->where('refinement_id', $refinementId);
            } else {
                $scopeQuery->whereNull('refinement_id');
            }

            $scopeQuery->update(['is_current' => false]);

            $versionQuery = PatientTreatmentPlan::query()
                ->where('patient_id', $patient->id)
                ->whereNull('stage_number');

            if ($refinementId) {
                $versionQuery->where('refinement_id', $refinementId);
            } else {
                $versionQuery->whereNull('refinement_id');
            }

            $version = (int) $versionQuery->max('version') + 1;

            PatientTreatmentPlan::create([
                'patient_id' => $patient->id,
                'refinement_id' => $refinementId,
                'stage_number' => null,
                'plan_url' => $validated['plan_url'],
                'review_status' => PatientTreatmentPlan::STATUS_PENDING,
                'review_comment' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'uploaded_by' => auth()->id(),
                'version' => max(1, $version),
                'is_current' => true,
            ]);

            $this->workflow->afterPlanUploaded($patient, $activeModification);
        });

        $patient->load('doctor.user');
        app(LineUpNotifier::class)->planUploaded($patient, auth()->user(), null);

        return $this->redirectToTab($patient, 'Treatment plan submitted for doctor review.');
    }

    public function storeStage(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('uploadTreatmentPlan', $patient);

        if (! $patient->isDividedStages()) {
            return $this->redirectToTab($patient, 'Stages apply only to divided-stage cases.', 'error');
        }

        $validated = $request->validate([
            'stage_number' => ['required', 'integer', 'min:1', 'max:99'],
            'step_from' => ['required', 'integer', 'min:1', 'max:999'],
            'step_to' => ['required', 'integer', 'min:1', 'max:999', 'gte:step_from'],
            'plan_url' => ['required', 'url', 'max:2048'],
        ]);

        $stageNumber = (int) $validated['stage_number'];
        $stepFrom = (int) $validated['step_from'];
        $stepTo = (int) $validated['step_to'];

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

        DB::transaction(function () use ($patient, $validated, $stageNumber, $stepFrom, $stepTo) {
            $activeModification = $patient->currentModification($stageNumber);
            $refinementId = $patient->activeRefinementId();

            $scopeQuery = PatientTreatmentPlan::query()
                ->where('patient_id', $patient->id)
                ->where('stage_number', $stageNumber)
                ->where('is_current', true);

            if ($refinementId) {
                $scopeQuery->where('refinement_id', $refinementId);
            } else {
                $scopeQuery->whereNull('refinement_id');
            }

            $scopeQuery->update(['is_current' => false]);

            $versionQuery = PatientTreatmentPlan::query()
                ->where('patient_id', $patient->id)
                ->where('stage_number', $stageNumber);

            if ($refinementId) {
                $versionQuery->where('refinement_id', $refinementId);
            } else {
                $versionQuery->whereNull('refinement_id');
            }

            $version = (int) $versionQuery->max('version') + 1;

            PatientTreatmentPlan::create([
                'patient_id' => $patient->id,
                'refinement_id' => $refinementId,
                'stage_number' => $stageNumber,
                'step_from' => $stepFrom,
                'step_to' => $stepTo,
                'plan_url' => $validated['plan_url'],
                'review_status' => PatientTreatmentPlan::STATUS_PENDING,
                'review_comment' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'uploaded_by' => auth()->id(),
                'version' => max(1, $version),
                'is_current' => true,
            ]);

            $this->workflow->afterPlanUploaded($patient, $activeModification);
        });

        $patient->load('doctor.user');
        app(LineUpNotifier::class)->planUploaded($patient, auth()->user(), $stageNumber);

        $range = $stepFrom === $stepTo ? "step {$stepFrom}" : "steps {$stepFrom}–{$stepTo}";

        return $this->redirectToTab(
            $patient,
            "Stage {$stageNumber} ({$range}) submitted for doctor review.",
            'success',
            $stageNumber
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
            return $this->redirectToTab($patient, 'Please add a comment when rejecting the plan so LineUp can revise it.', 'error');
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

        $message = $validated['decision'] === 'approved'
            ? ($plan->refinement_id
                ? 'Refinement plan approved. This refinement cycle is complete. The patient may order a new refinement when they return.'
                : 'Treatment plan approved. You may start a new modification cycle from the Request Modification tab when needed.')
            : 'Treatment plan rejected. LineUp admin will upload a revised plan.';

        $activeStage = $plan->stage_number;

        return $this->redirectToTab($patient, $message, 'success', $activeStage);
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
        ?int $activeStage = null
    ): RedirectResponse {
        $redirect = redirect()
            ->route('patients.show', $patient)
            ->with($type, $message)
            ->with('open_tab', 'manufacture-plan');

        if ($activeStage !== null) {
            $redirect->with('mfg_active_stage', $activeStage);
        }

        return $redirect;
    }
}
