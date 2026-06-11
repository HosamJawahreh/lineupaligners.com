<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientManufacturingStage;
use App\Models\PatientTreatmentPlan;
use App\Services\CaseWorkflowService;
use App\Services\LineUpNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientManufacturingController extends Controller
{
    public function __construct(
        protected CaseWorkflowService $workflow,
        protected LineUpNotifier $notifier
    ) {}

    public function markManufactured(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('markAsManufactured', $patient);

        if (! $patient->isReadyForManufacturedMark()) {
            return redirect()
                ->route('patients.show', $patient)
                ->with('error', 'This case is not ready to mark as manufactured. All plans must be doctor-approved first.')
                ->with('open_tab', 'manufacture-plan');
        }

        $this->workflow->afterMarkedManufactured($patient, (int) auth()->id());

        $patient->load('doctor.user');
        $this->notifier->caseMarkedManufactured($patient, auth()->user());

        $redirect = redirect()
            ->route('patients.show', $patient)
            ->with('success', 'Case marked as manufactured. The doctor may order refinement when the patient returns; modifications are closed for this cycle.')
            ->with('open_tab', 'manufacture-plan');

        if ($request->boolean('from_index')) {
            return redirect()
                ->route('patients.index')
                ->with('success', "Case {$patient->display_patient_id} marked as manufactured.");
        }

        return $redirect;
    }

    public function markStageManufactured(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('markAsManufactured', $patient);

        $validated = $request->validate([
            'stage_number' => ['required', 'integer', 'min:1', 'max:99'],
            'manufactured_step_from' => ['required', 'integer', 'min:1', 'max:999'],
            'manufactured_step_to' => ['required', 'integer', 'min:1', 'max:999', 'gte:manufactured_step_from'],
        ]);

        $stageNumber = (int) $validated['stage_number'];

        if (! $patient->isStageReadyForManufacturedMark($stageNumber)) {
            return redirect()
                ->route('patients.show', $patient)
                ->with('error', "Stage {$stageNumber} is not ready to mark as manufactured.")
                ->with('open_tab', 'manufacture-plan')
                ->with('mfg_active_stage', $stageNumber);
        }

        PatientManufacturingStage::create([
            'patient_id' => $patient->id,
            'refinement_id' => $patient->activeRefinementId(),
            'stage_number' => $stageNumber,
            'manufactured_step_from' => (int) $validated['manufactured_step_from'],
            'manufactured_step_to' => (int) $validated['manufactured_step_to'],
            'manufactured_at' => now(),
            'manufactured_by' => auth()->id(),
        ]);

        $patient->refresh();
        $fullyManufactured = $this->workflow->afterStageMarkedManufactured(
            $patient,
            $patient->currentFullTreatmentPlan() ?? $patient->originalCycleFullTreatmentPlan(),
            (int) auth()->id()
        );

        $patient->load('doctor.user');
        $stage = $patient->manufacturingStageRecord($stageNumber);
        $range = $stage?->stepRangeLabel() ?? '';

        if ($fullyManufactured) {
            $this->notifier->caseMarkedManufactured($patient, auth()->user());
        } else {
            $this->notifier->stageMarkedManufactured($patient, auth()->user(), $stageNumber, $range !== '' ? $range : null);
        }

        $message = $range !== ''
            ? "Manufacturing stage {$stageNumber} ({$range}) recorded."
            : "Manufacturing stage {$stageNumber} recorded.";

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', $message)
            ->with('open_tab', 'manufacture-plan')
            ->with('mfg_active_stage', $stageNumber);
    }
}
