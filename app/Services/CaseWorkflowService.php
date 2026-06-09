<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientCaseModification;
use App\Models\PatientCaseRefinement;
use App\Models\PatientTreatmentPlan;

class CaseWorkflowService
{
    public function afterMarkedManufactured(Patient $patient, int $adminUserId): void
    {
        PatientCaseRefinement::query()
            ->where('patient_id', $patient->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        $patient->update([
            'case_workflow_stage' => 'manufactured',
            'status' => Patient::STATUS_ACTIVE,
            'manufactured_at' => now(),
            'manufactured_by' => $adminUserId,
        ]);
    }

    public function afterStageMarkedManufactured(
        Patient $patient,
        ?PatientTreatmentPlan $plan,
        int $adminUserId
    ): bool {
        if ($patient->isDividedStages() && ! $patient->hasActiveRefinement()) {
            $patient->update([
                'case_workflow_stage' => 'approved',
                'status' => Patient::STATUS_ACTIVE,
            ]);

            return false;
        }

        $this->afterMarkedManufactured($patient, $adminUserId);

        return true;
    }

    public function afterModificationRequested(Patient $patient): void
    {
        $patient->update([
            'case_workflow_stage' => 'modification',
            'status' => 'pending',
        ]);
    }

    public function afterRefinementRequested(Patient $patient): void
    {
        $patient->update([
            'case_workflow_stage' => 'refinement',
            'status' => 'pending',
            'manufactured_at' => null,
            'manufactured_by' => null,
        ]);
    }

    public function afterPlanUploaded(
        Patient $patient,
        ?PatientCaseModification $closedModification = null,
        ?PatientCaseRefinement $closedRefinement = null
    ): void {
        if ($closedModification !== null) {
            $closedModification->update(['is_current' => false]);
        }

        if ($closedRefinement !== null) {
            $closedRefinement->update(['is_current' => false]);
        }

        if ($patient->workflowStageKey() === 'created') {
            $patient->update(['case_workflow_stage' => 'waiting_plan']);
        }

        $this->syncFromPlans($patient);
    }

    public function afterPlanReview(Patient $patient, ?PatientTreatmentPlan $reviewedPlan = null): void
    {
        if ($reviewedPlan?->isApproved()) {
            if ($reviewedPlan->refinement_id) {
                $this->closeActiveRefinementsForScope($patient);
            } else {
                $this->closeActiveModificationsForScope($patient, null);
            }
        }

        $this->syncFromPlans($patient);
    }

    protected function closeActiveModificationsForScope(Patient $patient, ?int $stageNumber): void
    {
        PatientCaseModification::query()
            ->where('patient_id', $patient->id)
            ->where('is_current', true)
            ->when($stageNumber === null, fn ($q) => $q->whereNull('stage_number'))
            ->when($stageNumber !== null, fn ($q) => $q->where('stage_number', $stageNumber))
            ->update(['is_current' => false]);
    }

    protected function closeActiveRefinementsForScope(Patient $patient): void
    {
        PatientCaseRefinement::query()
            ->where('patient_id', $patient->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);
    }

    public function syncFromPlans(Patient $patient): void
    {
        $patient->refresh();

        if ($patient->hasActiveRefinement()) {
            $this->syncRefinementCycle($patient);

            return;
        }

        if ($patient->isDividedStages()) {
            $this->syncDividedStages($patient);

            return;
        }

        $this->syncFullCase($patient);
    }

    protected function syncRefinementCycle(Patient $patient): void
    {
        if ($patient->isDividedStages()) {
            $stages = $patient->currentStageTreatmentPlans();

            if ($stages->isEmpty()) {
                $patient->update([
                    'case_workflow_stage' => 'refinement',
                    'status' => 'pending',
                ]);

                return;
            }

            if ($stages->contains(fn (PatientTreatmentPlan $plan) => $plan->isRejected())) {
                $patient->update([
                    'case_workflow_stage' => 'waiting_plan',
                    'status' => 'rejected',
                ]);

                return;
            }

            if ($stages->every(fn (PatientTreatmentPlan $plan) => $plan->isApproved())) {
                $patient->update([
                    'case_workflow_stage' => 'approved',
                    'status' => Patient::STATUS_ACTIVE,
                ]);

                return;
            }

            $patient->update([
                'case_workflow_stage' => 'waiting_plan',
                'status' => 'pending',
            ]);

            return;
        }

        $plan = $patient->currentFullTreatmentPlan();

        if (! $plan) {
            $patient->update([
                'case_workflow_stage' => 'refinement',
                'status' => 'pending',
            ]);

            return;
        }

        if ($plan->isApproved()) {
            $patient->update([
                'case_workflow_stage' => 'approved',
                'status' => Patient::STATUS_ACTIVE,
            ]);

            return;
        }

        if ($plan->isRejected()) {
            $patient->update([
                'case_workflow_stage' => 'waiting_plan',
                'status' => 'rejected',
            ]);

            return;
        }

        $patient->update([
            'case_workflow_stage' => 'waiting_plan',
            'status' => 'pending',
        ]);
    }

    protected function syncFullCase(Patient $patient): void
    {
        if ($patient->hasActiveModificationFor(null)) {
            $plan = $patient->currentFullTreatmentPlan();

            if (! $plan || $plan->isApproved()) {
                $patient->update([
                    'case_workflow_stage' => 'modification',
                    'status' => 'pending',
                ]);

                return;
            }

            if ($plan->isPending()) {
                $patient->update([
                    'case_workflow_stage' => 'waiting_plan',
                    'status' => 'pending',
                ]);

                return;
            }
        }

        $plan = $patient->currentFullTreatmentPlan();

        if (! $plan) {
            return;
        }

        if ($plan->isApproved()) {
            $patient->update([
                'case_workflow_stage' => 'approved',
                'status' => Patient::STATUS_ACTIVE,
            ]);

            return;
        }

        if ($plan->isRejected()) {
            $patient->update([
                'case_workflow_stage' => 'waiting_plan',
                'status' => 'rejected',
            ]);

            return;
        }

        $patient->update([
            'case_workflow_stage' => 'waiting_plan',
            'status' => 'pending',
        ]);
    }

    protected function syncDividedStages(Patient $patient): void
    {
        $this->syncFullCase($patient);
    }
}
