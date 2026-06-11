@php
    $ctx = $context ?? [];
    $ctxKey = $ctx['key'] ?? 'original';
    $ctxType = $ctx['type'] ?? 'original';
    $isActiveCtx = ! empty($ctx['is_active']);
    $isDivided = $patient->isDividedStages();
    $isDefault = ($defaultTreatmentPlanContextKey ?? 'original') === $ctxKey;
@endphp

<div class="mfg-plan__context-panel @if($isDefault) is-active @endif"
     id="mfg-context-panel-{{ $ctxKey }}"
     data-mfg-context-panel="{{ $ctxKey }}"
     @if(! $isDefault) hidden @endif>

    @php $ctxFullPlan = $ctx['full_plan'] ?? null; @endphp
    @if($isActiveCtx && $ctxType === 'refinement' && $ctxFullPlan?->isApproved())
    <div class="mfg-plan__mod-banner mfg-plan__mod-banner--refinement mfg-plan__mod-banner--refinement-approved" role="status">
        <i class="zmdi zmdi-check-circle" aria-hidden="true"></i>
        <div>
            <strong>Refinement plan approved</strong>
            <p>
                @if($isDivided)
                    The doctor approved this refinement treatment plan. Record each manufacturing batch in the stages section below, then mark the refinement cycle complete when production is finished.
                @else
                    The doctor approved this refinement treatment plan. LineUp can mark this refinement cycle as manufactured when production is complete.
                @endif
            </p>
        </div>
    </div>
    @elseif($isActiveCtx && $ctxType === 'refinement')
    <div class="mfg-plan__mod-banner mfg-plan__mod-banner--refinement" role="status">
        <i class="zmdi zmdi-swap-vertical" aria-hidden="true"></i>
        <div>
            <strong>Refinement case in progress</strong>
            <p>The doctor ordered a refinement (returning patient). Upload a new treatment plan canvas link for this refinement cycle when ready — previous manufactured plans stay in the plan switcher and 3D Scans &amp; Photos.</p>
            @if($patient->awaitingRefinementTreatmentPlanUpload() && ($canUploadTreatmentPlan ?? false))
            <p class="mfg-plan__mod-banner-hint"><strong>No refinement plan yet.</strong> Use the upload form below to add one.</p>
            @endif
        </div>
    </div>
    @elseif($isActiveCtx && $ctxType === 'original' && $patient->hasActiveModificationFor(null))
    <div class="mfg-plan__mod-banner" role="status">
        <i class="zmdi zmdi-refresh-sync" aria-hidden="true"></i>
        <div>
            <strong>Modification requested</strong>
            <p>The doctor uploaded new 3D scans and notes. Upload a revised treatment plan canvas link below for review.</p>
        </div>
    </div>
    @endif

    @php
            $visibleFullPlans = $ctx['visible_full_plans'] ?? collect();
            $fullPlan = $ctx['full_plan'] ?? null;
            $canUploadFull = $isActiveCtx && ($canAdminUploadFullPlan ?? false) && ($canUploadTreatmentPlan ?? false);
        @endphp
        @if($visibleFullPlans->isEmpty())
        <div class="mfg-plan__empty">
            <i class="zmdi zmdi-assignment" aria-hidden="true"></i>
            <p>No treatment plan has been uploaded for {{ $ctx['label'] ?? 'this cycle' }}.</p>
            @if($canUploadFull)
            <span class="mfg-plan__empty-hint">Paste the canvas link from the LineUp viewer below.</span>
            @endif
        </div>
        @php
            $showManufacturedBanner = $isActiveCtx && ! $isDivided
                && ! ($ctxType === 'refinement' && $patient->awaitingRefinementTreatmentPlanUpload())
                && (
                    $patient->hasCompletedManufacturing()
                    || (($canMarkManufactured ?? false) && $patient->isReadyForManufacturedMark())
                );
        @endphp
        @if($showManufacturedBanner)
        @include('theme.pages.partials.case-manufacture-plan-manufactured-banner', [
            'patient' => $patient,
            'canMarkManufactured' => $canMarkManufactured ?? false,
        ])
        @endif
        @else
        @include('theme.pages.partials.case-manufacture-plan-versions', [
            'plans' => $visibleFullPlans,
            'patient' => $patient,
            'navKey' => $ctxKey,
            'canReview' => $isActiveCtx && ($canReviewTreatmentPlan ?? false),
            'canUpload' => $canUploadTreatmentPlan ?? false,
            'canMarkManufactured' => $isActiveCtx && ($canMarkManufactured ?? false) && ! $isDivided,
        ])
        @endif

        @php
            $canSubmitFullPlan = $isActiveCtx && $canUploadFull && (
                $fullPlan === null
                || $fullPlan->isRejected()
                || $patient->hasActiveModificationFor(null)
            );
            $isRefinementUpload = $ctxType === 'refinement' && $patient->hasActiveRefinement();
        @endphp
        @if($canSubmitFullPlan)
        <section class="mfg-plan__panel mfg-plan__panel--admin mfg-plan__panel--revision">
            <h4 class="mfg-plan__panel-title">
                <i class="zmdi zmdi-link"></i>
                @if($isRefinementUpload && $fullPlan === null)
                    Upload refinement treatment plan
                @elseif($isRefinementUpload && $fullPlan?->isRejected())
                    Submit revised refinement plan
                @elseif($fullPlan && $patient->hasActiveModificationFor(null) && $fullPlan->isPending())
                    Upload revised plan after modification
                @elseif($fullPlan && $patient->hasActiveModificationFor(null))
                    Upload plan after modification
                @elseif($fullPlan && $fullPlan->isRejected())
                    Submit revised plan
                @else
                    Upload treatment plan
                @endif
            </h4>
            @if($isRefinementUpload && $fullPlan === null)
            <p class="mfg-plan__panel-desc">Optional — add a canvas link for this refinement cycle when LineUp has prepared the plan.</p>
            @endif
            <form method="post" action="{{ route('patients.treatment-plan.store', $patient) }}" class="mfg-plan__form">
                @csrf
                <div class="mfg-plan__field">
                    <label for="mfg-full-url-{{ $ctxKey }}">Treatment plan canvas link</label>
                    <input type="url" id="mfg-full-url-{{ $ctxKey }}" name="plan_url" value="{{ old('plan_url') }}" placeholder="https://viewer.lineup.com/…" required>
                </div>
                <button type="submit" class="mfg-plan__btn mfg-plan__btn--primary">
                    {{ $fullPlan ? 'Submit revision for review' : 'Submit plan for review' }}
                </button>
            </form>
        </section>
        @elseif($isActiveCtx && $canUploadTreatmentPlan && $fullPlan?->isPending())
        <p class="mfg-plan__locked-note"><i class="zmdi zmdi-lock"></i> Awaiting doctor review — upload is disabled until the plan is rejected.</p>
        @elseif($isActiveCtx && $canUploadTreatmentPlan && $fullPlan?->isApproved() && ! $patient->hasActiveModificationFor(null) && ! $isDivided)
        <p class="mfg-plan__locked-note mfg-plan__locked-note--ok"><i class="zmdi zmdi-check-circle"></i> This plan is approved. No further upload is required until the doctor requests a modification.</p>
        @endif

        @if($isDivided && $isActiveCtx && in_array($ctxType, ['original', 'refinement'], true))
        @include('theme.pages.partials.case-manufacture-plan-divided-mfg', [
            'patient' => $patient,
            'canMarkManufactured' => $canMarkManufactured ?? false,
            'isRefinementCycle' => $ctxType === 'refinement',
        ])
        @endif
</div>
