<?php

namespace App\Http\Controllers;

use App\Models\Patient;
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
                ->with('success', "Case {$patient->patient_id} marked as manufactured.");
        }

        return $redirect;
    }
}
