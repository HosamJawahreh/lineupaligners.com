@php
    $plan = $plan ?? null;
    $patient = $patient ?? null;
    $canMarkManufactured = $canMarkManufactured ?? false;

    if (! $plan || ! $patient || $plan->stage_number === null) {
        return;
    }

    $stageNum = (int) $plan->stage_number;
    $isManufactured = $plan->isManufactured();
    $canMarkStage = $canMarkManufactured && $patient->isStageReadyForManufacturedMark($stageNum);
    $suggestedMfgFrom = (int) old('manufactured_step_from', $patient->suggestedManufacturedStepFrom($stageNum));
    $suggestedMfgTo = (int) old('manufactured_step_to', $suggestedMfgFrom);
@endphp

@if($isManufactured)
<div class="mfg-plan__mod-banner mfg-plan__mod-banner--manufactured mfg-plan__mod-banner--stage is-complete" role="status">
    <i class="zmdi zmdi-check-circle" aria-hidden="true"></i>
    <div>
        <strong>Stage {{ $stageNum }} manufactured</strong>
        <p>
            @if($plan->manufactured_step_from && $plan->manufactured_step_to)
                {{ $plan->manufacturedStepRangeLabel() }} marked manufactured
            @else
                This stage is marked manufactured.
            @endif
            @if($plan->manufactured_at)
                · {{ $plan->manufactured_at->format('M j, Y g:i A') }}
            @endif
        </p>
    </div>
</div>
@elseif($plan->isApproved() && $canMarkManufactured && $stageNum > 1)
@php $previousStagePlan = $patient->currentTreatmentPlanForStage($stageNum - 1); @endphp
@if($previousStagePlan && ! $previousStagePlan->isManufactured())
<div class="mfg-plan__mod-banner mfg-plan__mod-banner--info mfg-plan__mod-banner--stage" role="status">
    <i class="zmdi zmdi-info-outline" aria-hidden="true"></i>
    <div>
        <strong>Stage {{ $stageNum }} approved</strong>
        <p>Mark stage {{ $stageNum - 1 }} as manufactured first, then you can enter the manufacturing step range for this stage.</p>
    </div>
</div>
@endif
@elseif($canMarkStage)
<div class="mfg-plan__mod-banner mfg-plan__mod-banner--manufactured mfg-plan__mod-banner--stage" role="status">
    <i class="zmdi zmdi-check-circle" aria-hidden="true"></i>
    <div>
        <strong>Ready to mark stage {{ $stageNum }} manufactured</strong>
        <p>Confirm the manufacturing step range for this stage. Modifications close for the full case once every stage is marked manufactured.</p>
        <form method="post"
              action="{{ route('patients.mark-stage-manufactured', $patient) }}"
              class="mfg-plan__mark-form mfg-plan__mark-form--stage">
            @csrf
            <input type="hidden" name="stage_number" value="{{ $stageNum }}">
            <div class="mfg-plan__mark-stage-fields">
                <div class="mfg-plan__field mfg-plan__field--narrow">
                    <label for="mfg-mfg-from-{{ $stageNum }}">Manufacturing steps from</label>
                    <input type="number"
                           id="mfg-mfg-from-{{ $stageNum }}"
                           name="manufactured_step_from"
                           min="1"
                           max="999"
                           value="{{ $suggestedMfgFrom }}"
                           data-mfg-manufacturing-step-from
                           required>
                </div>
                <div class="mfg-plan__field mfg-plan__field--narrow">
                    <label for="mfg-mfg-to-{{ $stageNum }}">Manufacturing steps to</label>
                    <input type="number"
                           id="mfg-mfg-to-{{ $stageNum }}"
                           name="manufactured_step_to"
                           min="1"
                           max="999"
                           value="{{ $suggestedMfgTo }}"
                           data-mfg-manufacturing-step-to
                           required>
                </div>
            </div>
            <button type="submit" class="mfg-plan__btn mfg-plan__btn--manufactured">
                <i class="zmdi zmdi-check-circle"></i> Mark stage {{ $stageNum }} manufactured
            </button>
        </form>
    </div>
</div>
@endif
