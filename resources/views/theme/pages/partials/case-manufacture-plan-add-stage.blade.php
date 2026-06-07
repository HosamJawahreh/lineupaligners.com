@php
    $suggestedStage = (int) ($suggestedStage ?? 1);
    $suggestedStepFrom = (int) ($suggestedStepFrom ?? 1);
    $canAdd = (bool) ($canAdd ?? false);
    $blockedReason = $blockedReason ?? null;
    $showBlocked = ! $canAdd && ($canUploadTreatmentPlan ?? false) && $blockedReason;
@endphp

@if($canAdd)
<section class="mfg-plan__add-stage" aria-labelledby="mfg-add-stage-title">
    <div class="mfg-plan__add-stage-accent" aria-hidden="true"></div>
    <header class="mfg-plan__add-stage-head">
        <span class="mfg-plan__add-stage-icon" aria-hidden="true">
            <i class="zmdi zmdi-layers"></i>
        </span>
        <div class="mfg-plan__add-stage-head-text">
            <p class="mfg-plan__add-stage-kicker">Next in sequence</p>
            <h4 class="mfg-plan__add-stage-title" id="mfg-add-stage-title">Add stage {{ $suggestedStage }}</h4>
            <p class="mfg-plan__add-stage-lead">Define the step range and paste the LineUp viewer link. The doctor will review this stage before you can add another.</p>
        </div>
    </header>

    <form method="post"
          action="{{ route('patients.treatment-plan.stage.store', $patient) }}"
          class="mfg-plan__add-stage-form">
        @csrf

        <div class="mfg-plan__add-stage-fields">
            <div class="mfg-plan__add-stage-field mfg-plan__add-stage-field--stage">
                <label for="mfg-stage-number">Stage</label>
                <div class="mfg-plan__add-stage-input-wrap">
                    <span class="mfg-plan__add-stage-input-prefix" aria-hidden="true">#</span>
                    <input type="number"
                           id="mfg-stage-number"
                           name="stage_number"
                           min="1"
                           max="99"
                           value="{{ old('stage_number', $suggestedStage) }}"
                           required
                           readonly
                           class="mfg-plan__add-stage-input mfg-plan__add-stage-input--readonly">
                </div>
            </div>

            <div class="mfg-plan__add-stage-range">
                <div class="mfg-plan__add-stage-field">
                    <label for="mfg-step-from">Steps from</label>
                    <input type="number"
                           id="mfg-step-from"
                           name="step_from"
                           min="1"
                           max="999"
                           value="{{ old('step_from', $suggestedStepFrom) }}"
                           required
                           class="mfg-plan__add-stage-input"
                           data-mfg-step-from>
                </div>
                <span class="mfg-plan__add-stage-range-sep" aria-hidden="true">
                    <i class="zmdi zmdi-arrow-right"></i>
                </span>
                <div class="mfg-plan__add-stage-field">
                    <label for="mfg-step-to">Steps to</label>
                    <input type="number"
                           id="mfg-step-to"
                           name="step_to"
                           min="1"
                           max="999"
                           value="{{ old('step_to', $suggestedStepFrom) }}"
                           required
                           class="mfg-plan__add-stage-input"
                           data-mfg-step-to>
                </div>
            </div>
        </div>

        <div class="mfg-plan__add-stage-field mfg-plan__add-stage-field--url">
            <label for="mfg-stage-url">Treatment plan canvas link</label>
            <div class="mfg-plan__add-stage-input-wrap mfg-plan__add-stage-input-wrap--url">
                <span class="mfg-plan__add-stage-input-prefix" aria-hidden="true">
                    <i class="zmdi zmdi-link"></i>
                </span>
                <input type="url"
                       id="mfg-stage-url"
                       name="plan_url"
                       value="{{ old('plan_url') }}"
                       placeholder="https://viewer.lineup.com/…"
                       required
                       class="mfg-plan__add-stage-input">
            </div>
            <span class="mfg-plan__add-stage-hint">Use the share link from the LineUp treatment plan viewer for this step range.</span>
        </div>

        <footer class="mfg-plan__add-stage-foot">
            <button type="submit" class="mfg-plan__add-stage-submit">
                <i class="zmdi zmdi-cloud-upload" aria-hidden="true"></i>
                Save stage &amp; send for review
            </button>
        </footer>
    </form>
</section>
@elseif($showBlocked)
<section class="mfg-plan__add-stage mfg-plan__add-stage--locked" role="status">
    <div class="mfg-plan__add-stage-accent mfg-plan__add-stage-accent--muted" aria-hidden="true"></div>
    <div class="mfg-plan__add-stage-locked">
        <span class="mfg-plan__add-stage-locked-icon" aria-hidden="true">
            <i class="zmdi zmdi-lock"></i>
        </span>
        <div>
            <p class="mfg-plan__add-stage-locked-title">Next stage not available yet</p>
            <p class="mfg-plan__add-stage-locked-text">{{ $blockedReason }}</p>
        </div>
    </div>
</section>
@endif
