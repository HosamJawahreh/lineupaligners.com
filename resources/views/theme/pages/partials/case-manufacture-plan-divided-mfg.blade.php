@php
    $patient = $patient ?? null;
    $canMarkManufactured = $canMarkManufactured ?? false;
    $mfgStages = $patient?->manufacturingStagesForScope() ?? collect();
    $nextStage = $patient?->nextManufacturingStageNumber() ?? 1;
    $canMarkNextStage = $canMarkManufactured && $patient?->isStageReadyForManufacturedMark($nextStage);
    $fullPlan = $patient?->currentFullTreatmentPlan() ?? $patient?->originalCycleFullTreatmentPlan();
    $suggestedFrom = (int) old('manufactured_step_from', $patient?->suggestedManufacturedStepFrom($nextStage) ?? 1);
    $suggestedTo = (int) old('manufactured_step_to', $suggestedFrom);
@endphp

<section class="mfg-plan__divided-mfg" aria-labelledby="mfg-divided-mfg-title">
    <header class="mfg-plan__divided-mfg-head">
        <h4 class="mfg-plan__divided-mfg-title" id="mfg-divided-mfg-title">
            <i class="zmdi zmdi-layers" aria-hidden="true"></i>
            Manufacturing stages
        </h4>
        <p class="mfg-plan__divided-mfg-lead">
            This case uses one shared treatment plan above. Record each manufacturing batch here with its step range after the doctor approves the plan.
        </p>
    </header>

    @if($mfgStages->isNotEmpty())
    <ul class="mfg-plan__divided-mfg-list">
        @foreach($mfgStages as $stage)
        <li class="mfg-plan__divided-mfg-item is-complete">
            <span class="mfg-plan__divided-mfg-item-icon" aria-hidden="true"><i class="zmdi zmdi-check-circle"></i></span>
            <div>
                <strong>Stage {{ $stage->stage_number }}</strong>
                <span>{{ $stage->stepRangeLabel() }}</span>
                @if($stage->manufactured_at)
                <span class="mfg-plan__divided-mfg-item-date">· {{ $stage->manufactured_at->format('M j, Y g:i A') }}</span>
                @endif
            </div>
        </li>
        @endforeach
    </ul>
    @endif

    @if($canMarkNextStage && $fullPlan?->isApproved())
    <div class="mfg-plan__mod-banner mfg-plan__mod-banner--manufactured mfg-plan__mod-banner--stage" role="status">
        <i class="zmdi zmdi-check-circle" aria-hidden="true"></i>
        <div>
            <strong>Ready to mark manufacturing stage {{ $nextStage }}</strong>
            <p>Enter the aligner step range manufactured in this batch.</p>
            <form method="post"
                  action="{{ route('patients.mark-stage-manufactured', $patient) }}"
                  class="mfg-plan__mark-form mfg-plan__mark-form--stage">
                @csrf
                <input type="hidden" name="stage_number" value="{{ $nextStage }}">
                <div class="mfg-plan__mark-stage-fields">
                    <div class="mfg-plan__field mfg-plan__field--narrow">
                        <label for="mfg-divided-from-{{ $nextStage }}">Steps from</label>
                        <input type="number"
                               id="mfg-divided-from-{{ $nextStage }}"
                               name="manufactured_step_from"
                               min="1"
                               max="999"
                               value="{{ $suggestedFrom }}"
                               data-mfg-manufacturing-step-from
                               required>
                    </div>
                    <div class="mfg-plan__field mfg-plan__field--narrow">
                        <label for="mfg-divided-to-{{ $nextStage }}">Steps to</label>
                        <input type="number"
                               id="mfg-divided-to-{{ $nextStage }}"
                               name="manufactured_step_to"
                               min="1"
                               max="999"
                               value="{{ $suggestedTo }}"
                               data-mfg-manufacturing-step-to
                               required>
                    </div>
                </div>
                <button type="submit" class="mfg-plan__btn mfg-plan__btn--manufactured">
                    <i class="zmdi zmdi-check-circle"></i> Mark stage {{ $nextStage }} manufactured
                </button>
            </form>
        </div>
    </div>
    @elseif($fullPlan?->isApproved() && ! $patient->hasCompletedManufacturing())
    <p class="mfg-plan__locked-note mfg-plan__locked-note--ok">
        <i class="zmdi zmdi-check-circle"></i>
        Plan approved. Mark each manufacturing stage above when a batch is complete.
    </p>
    @endif

    @if(($canMarkManufactured ?? false) && $patient->isReadyForManufacturedMark())
    @include('theme.pages.partials.case-manufacture-plan-manufactured-banner', [
        'patient' => $patient,
        'canMarkManufactured' => $canMarkManufactured,
        'dividedCase' => true,
    ])
    @elseif($patient->hasCompletedManufacturing())
    @include('theme.pages.partials.case-manufacture-plan-manufactured-banner', [
        'patient' => $patient,
        'canMarkManufactured' => $canMarkManufactured,
        'dividedCase' => true,
    ])
    @endif
</section>
