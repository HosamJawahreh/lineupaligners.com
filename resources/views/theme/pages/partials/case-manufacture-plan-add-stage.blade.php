@php
    $suggestedStage = (int) ($suggestedStage ?? 1);
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
            <p class="mfg-plan__add-stage-lead">Paste the LineUp viewer link for this stage. The doctor will review it before you can add the next stage. Manufacturing step numbers are entered when you mark each stage as manufactured.</p>
        </div>
    </header>

    <form method="post"
          action="{{ route('patients.treatment-plan.stage.store', $patient) }}"
          class="mfg-plan__add-stage-form">
        @csrf
        <input type="hidden" name="stage_number" value="{{ old('stage_number', $suggestedStage) }}">

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
            <span class="mfg-plan__add-stage-hint">Use the share link from the LineUp treatment plan viewer for stage {{ $suggestedStage }}.</span>
        </div>

        <footer class="mfg-plan__add-stage-foot">
            <button type="submit" class="mfg-plan__add-stage-submit">
                <i class="zmdi zmdi-cloud-upload" aria-hidden="true"></i>
                Save stage {{ $suggestedStage }} &amp; send for review
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
