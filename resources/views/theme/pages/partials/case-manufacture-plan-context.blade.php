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

    @if($isActiveCtx && $ctxType === 'refinement')
    <div class="mfg-plan__mod-banner mfg-plan__mod-banner--refinement" role="status">
        <i class="zmdi zmdi-swap-vertical" aria-hidden="true"></i>
        <div>
            <strong>Refinement case in progress</strong>
            <p>The doctor ordered a refinement (returning patient). Upload new plan link(s) below. Previous manufactured plans remain available in the plan switcher and 3D Scans &amp; Photos.</p>
        </div>
    </div>
    @elseif($isActiveCtx && $ctxType === 'modification')
    <div class="mfg-plan__mod-banner" role="status">
        <i class="zmdi zmdi-refresh-sync" aria-hidden="true"></i>
        <div>
            <strong>Modification requested</strong>
            <p>The doctor uploaded new 3D scans and notes. Upload a revised treatment plan canvas link below for review.</p>
        </div>
    </div>
    @endif

    @if($ctxType === 'modification')
        @php
            $mod = $ctx['modification'] ?? null;
            $planUrl = $ctx['plan_url'] ?? null;
            $linkedPlan = $ctx['treatment_plan'] ?? null;
            $reviewStatus = $ctx['review_status'] ?? 'pending';
            $statusClass = match ($reviewStatus) {
                'approved' => 'is-approved',
                'rejected' => 'is-rejected',
                default => 'is-pending',
            };
        @endphp
        @if(filled($planUrl))
        <article class="mfg-plan__card {{ $statusClass }}">
            <header class="mfg-plan__card-head">
                <div>
                    <h4 class="mfg-plan__card-title">{{ $ctx['label'] ?? 'Modification plan' }}</h4>
                    <span class="mfg-plan__status mfg-plan__status--{{ $reviewStatus }}">{{ ucfirst($reviewStatus) }}</span>
                </div>
            </header>
            @if($linkedPlan && $isActiveCtx && ($canReviewTreatmentPlan ?? false) && $linkedPlan->isPending() && $linkedPlan->is_current)
            @include('theme.pages.partials.case-manufacture-plan-doctor-actions', [
                'plan' => $linkedPlan,
                'patient' => $patient,
                'canReview' => $canReviewTreatmentPlan ?? false,
                'isHistorical' => false,
            ])
            @endif
            <div class="mfg-plan__canvas-wrap @if($linkedPlan && $isActiveCtx && ($canReviewTreatmentPlan ?? false) && $linkedPlan->isPending() && $linkedPlan->is_current) mfg-plan__canvas-wrap--with-actions @endif">
                <iframe src="{{ $planUrl }}"
                        title="{{ $ctx['label'] ?? 'Modification' }} treatment plan"
                        class="mfg-plan__canvas"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen></iframe>
            </div>
        </article>
        @elseif($isActiveCtx)
        <div class="mfg-plan__empty">
            <i class="zmdi zmdi-time" aria-hidden="true"></i>
            <p>Awaiting revised plan upload for {{ $ctx['label'] ?? 'this modification' }}.</p>
            @if($canUploadTreatmentPlan ?? false)
            <span class="mfg-plan__empty-hint">Paste the canvas link below when ready.</span>
            @endif
        </div>
        @else
        <div class="mfg-plan__empty">
            <i class="zmdi zmdi-assignment" aria-hidden="true"></i>
            <p>No revised plan was uploaded for {{ $ctx['label'] ?? 'this modification' }}.</p>
        </div>
        @endif

        @if($isActiveCtx && ($canUploadTreatmentPlan ?? false) && $mod)
            @php $canUploadMod = $patient->canAdminUploadFullTreatmentPlan(); @endphp
            @if($canUploadMod)
            <section class="mfg-plan__panel mfg-plan__panel--admin mfg-plan__panel--revision">
                <h4 class="mfg-plan__panel-title"><i class="zmdi zmdi-link"></i> Upload revised plan after modification</h4>
                <p class="mfg-plan__panel-desc">The doctor requested changes. Upload the updated canvas link for review.</p>
                <form method="post" action="{{ route('patients.treatment-plan.store', $patient) }}" class="mfg-plan__form">
                    @csrf
                    <div class="mfg-plan__field">
                        <label for="mfg-ctx-mod-url-full-{{ $ctxKey }}">Treatment plan canvas link</label>
                        <input type="url" id="mfg-ctx-mod-url-full-{{ $ctxKey }}" name="plan_url" placeholder="https://viewer.lineup.com/…" required>
                    </div>
                    <button type="submit" class="mfg-plan__btn mfg-plan__btn--primary">Submit revised plan for review</button>
                </form>
            </section>
            @endif
        @endif
    @else
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
        @if($isActiveCtx && ! $isDivided && ($patient->hasCompletedManufacturing() || (($canMarkManufactured ?? false) && $patient->isReadyForManufacturedMark())))
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
        @endphp
        @if($canSubmitFullPlan)
        <section class="mfg-plan__panel mfg-plan__panel--admin mfg-plan__panel--revision">
            <h4 class="mfg-plan__panel-title">
                <i class="zmdi zmdi-link"></i>
                @if($fullPlan && $patient->hasActiveModificationFor(null) && $fullPlan->isPending())
                    Upload revised plan after modification
                @elseif($fullPlan && $patient->hasActiveModificationFor(null))
                    Upload plan after modification
                @elseif($fullPlan && $fullPlan->isRejected())
                    Submit revised plan
                @else
                    Upload treatment plan
                @endif
            </h4>
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

        @if($isDivided && $isActiveCtx && $ctxType === 'original')
        @include('theme.pages.partials.case-manufacture-plan-divided-mfg', [
            'patient' => $patient,
            'canMarkManufactured' => $canMarkManufactured ?? false,
        ])
        @endif
    @endif
</div>
