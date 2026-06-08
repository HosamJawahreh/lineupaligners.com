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
            <header class="mfg-plan__card-head mfg-plan__card-head--with-switcher">
                <div class="mfg-plan__card-head-text">
                    <h4 class="mfg-plan__card-title">{{ $ctx['label'] ?? 'Modification plan' }}</h4>
                    <span class="mfg-plan__status mfg-plan__status--{{ $reviewStatus }}">{{ ucfirst($reviewStatus) }}</span>
                </div>
                @include('theme.pages.partials.case-treatment-plan-context-switcher', [
                    'contexts' => $treatmentPlanContexts ?? [],
                    'defaultContextKey' => $defaultTreatmentPlanContextKey ?? 'original',
                    'selectId' => 'mfg-plan-context-select-'.$ctxKey,
                ])
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
        <article class="mfg-plan__card">
            <header class="mfg-plan__card-head mfg-plan__card-head--with-switcher">
                <div class="mfg-plan__card-head-text">
                    <h4 class="mfg-plan__card-title">{{ $ctx['label'] ?? 'Modification plan' }}</h4>
                    <span class="mfg-plan__status mfg-plan__status--pending">Awaiting plan</span>
                </div>
                @include('theme.pages.partials.case-treatment-plan-context-switcher', [
                    'contexts' => $treatmentPlanContexts ?? [],
                    'defaultContextKey' => $defaultTreatmentPlanContextKey ?? 'original',
                    'selectId' => 'mfg-plan-context-select-'.$ctxKey,
                ])
            </header>
        </article>
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
            @php
                $stageNum = $ctx['stage_number'] ?? null;
                $canUploadMod = $isDivided
                    ? $patient->canAdminUploadStageTreatmentPlan((int) $stageNum)
                    : $patient->canAdminUploadFullTreatmentPlan();
            @endphp
            @if($canUploadMod)
            <section class="mfg-plan__panel mfg-plan__panel--admin mfg-plan__panel--revision">
                <h4 class="mfg-plan__panel-title"><i class="zmdi zmdi-link"></i> Upload revised plan after modification</h4>
                <p class="mfg-plan__panel-desc">The doctor requested changes. Upload the updated canvas link for review.</p>
                @if($isDivided && $stageNum)
                <form method="post" action="{{ route('patients.treatment-plan.stage.store', $patient) }}" class="mfg-plan__form">
                    @csrf
                    <input type="hidden" name="stage_number" value="{{ $stageNum }}">
                    @php $stagePlan = $patient->currentTreatmentPlanForStage($stageNum); @endphp
                    <div class="mfg-plan__form-row">
                        <div class="mfg-plan__field mfg-plan__field--narrow">
                            <label for="mfg-ctx-mod-from-{{ $ctxKey }}">Steps from</label>
                            <input type="number" id="mfg-ctx-mod-from-{{ $ctxKey }}" name="step_from" min="1" max="999" value="{{ $stagePlan->step_from ?? 1 }}" required>
                        </div>
                        <div class="mfg-plan__field mfg-plan__field--narrow">
                            <label for="mfg-ctx-mod-to-{{ $ctxKey }}">Steps to</label>
                            <input type="number" id="mfg-ctx-mod-to-{{ $ctxKey }}" name="step_to" min="1" max="999" value="{{ $stagePlan->step_to ?? 1 }}" required>
                        </div>
                    </div>
                    <div class="mfg-plan__field">
                        <label for="mfg-ctx-mod-url-{{ $ctxKey }}">Revised canvas link</label>
                        <input type="url" id="mfg-ctx-mod-url-{{ $ctxKey }}" name="plan_url" placeholder="https://…" required>
                    </div>
                    <button type="submit" class="mfg-plan__btn mfg-plan__btn--primary">Submit revised plan for review</button>
                </form>
                @else
                <form method="post" action="{{ route('patients.treatment-plan.store', $patient) }}" class="mfg-plan__form">
                    @csrf
                    <div class="mfg-plan__field">
                        <label for="mfg-ctx-mod-url-full-{{ $ctxKey }}">Treatment plan canvas link</label>
                        <input type="url" id="mfg-ctx-mod-url-full-{{ $ctxKey }}" name="plan_url" placeholder="https://viewer.lineup.com/…" required>
                    </div>
                    <button type="submit" class="mfg-plan__btn mfg-plan__btn--primary">Submit revised plan for review</button>
                </form>
                @endif
            </section>
            @endif
        @endif
    @elseif($isDivided)
        @php
            $stagePlans = $ctx['stage_plans'] ?? collect();
            $stageNumbers = $ctx['stage_numbers'] ?? collect();
            $refinementId = $ctx['refinement_id'] ?? null;
            $suggestedStage = (int) ($stageNumbers->max() + 1);
            if ($stageNumbers->isEmpty()) {
                $suggestedStage = 1;
            }
            $suggestedStepFrom = (int) (old('step_from', $stagePlans->max('step_to') ? $stagePlans->max('step_to') + 1 : 1));
            $canAddNewStage = $isActiveCtx && ($canUploadTreatmentPlan ?? false) && $patient->canAdminAddNewDividedStage();
            $reviewStage = $isActiveCtx ? $patient->doctorReviewStageNumber() : null;
            $addStageBlockedReason = null;
            if ($isActiveCtx && ($canUploadTreatmentPlan ?? false) && ! $canAddNewStage) {
                $addStageBlockedReason = $reviewStage !== null
                    ? 'Stage '.$reviewStage.' is awaiting doctor approval. Add the next stage only after it is approved.'
                    : ($patient->hasActiveModificationForAny()
                        ? 'A modification is in progress. Upload the revised plan for the current stage before adding a new one.'
                        : 'Complete the current stage before adding another.');
            }
        @endphp

        @if($stageNumbers->isEmpty())
        <article class="mfg-plan__card">
            <header class="mfg-plan__card-head mfg-plan__card-head--with-switcher">
                <div class="mfg-plan__card-head-text">
                    <h4 class="mfg-plan__card-title">{{ $ctx['label'] ?? 'Treatment plan' }}</h4>
                    <span class="mfg-plan__status mfg-plan__status--pending">No stages yet</span>
                </div>
                @include('theme.pages.partials.case-treatment-plan-context-switcher', [
                    'contexts' => $treatmentPlanContexts ?? [],
                    'defaultContextKey' => $defaultTreatmentPlanContextKey ?? 'original',
                    'selectId' => 'mfg-plan-context-select-'.$ctxKey,
                ])
            </header>
        </article>
        <div class="mfg-plan__empty">
            <i class="zmdi zmdi-assignment-o" aria-hidden="true"></i>
            <p>No stage plans uploaded yet for {{ $ctx['label'] ?? 'this cycle' }}.</p>
            @if($isActiveCtx && ($canUploadTreatmentPlan ?? false))
            <span class="mfg-plan__empty-hint">Start with stage 1 — define the step range and canvas link below.</span>
            @endif
        </div>
        @if($isActiveCtx)
        @include('theme.pages.partials.case-manufacture-plan-add-stage', [
            'patient' => $patient,
            'canUploadTreatmentPlan' => $canUploadTreatmentPlan ?? false,
            'canAdd' => $canAddNewStage,
            'blockedReason' => $addStageBlockedReason,
            'suggestedStage' => $suggestedStage,
            'suggestedStepFrom' => $suggestedStepFrom,
        ])
        @endif
        @else
        @php
            $defaultStage = $reviewStage ?? (int) ($stageNumbers->max());
            $activeStage = (int) (session('mfg_active_stage') ?? $defaultStage);
            if (! $stageNumbers->contains($activeStage)) {
                $activeStage = (int) $stageNumbers->first();
            }
        @endphp
        <article class="mfg-plan__card mfg-plan__card--context-bar">
            <header class="mfg-plan__card-head mfg-plan__card-head--with-switcher">
                <div class="mfg-plan__card-head-text">
                    <h4 class="mfg-plan__card-title">{{ $ctx['label'] ?? 'Treatment plan' }}</h4>
                </div>
                @include('theme.pages.partials.case-treatment-plan-context-switcher', [
                    'contexts' => $treatmentPlanContexts ?? [],
                    'defaultContextKey' => $defaultTreatmentPlanContextKey ?? 'original',
                    'selectId' => 'mfg-plan-context-select-'.$ctxKey,
                ])
            </header>
        </article>
        <nav class="mfg-plan__stage-nav" aria-label="Treatment plan stages" data-mfg-stage-nav="{{ $ctxKey }}">
            <span class="mfg-plan__stage-nav-label">View stage</span>
            <div class="mfg-plan__stage-nav-buttons" role="tablist">
                @foreach($stagePlans as $plan)
                <button type="button"
                        role="tab"
                        class="mfg-plan__stage-btn @if($plan->stage_number === $activeStage) is-active @endif"
                        data-mfg-stage-btn
                        data-stage="{{ $plan->stage_number }}"
                        aria-selected="{{ $plan->stage_number === $activeStage ? 'true' : 'false' }}"
                        aria-controls="mfg-stage-panel-{{ $ctxKey }}-{{ $plan->stage_number }}"
                        id="mfg-stage-tab-{{ $ctxKey }}-{{ $plan->stage_number }}">
                    <span class="mfg-plan__stage-btn-num">Stage {{ $plan->stage_number }}</span>
                    @if($plan->hasStepRange())
                    <span class="mfg-plan__stage-btn-range">{{ $plan->stepRangeLabel() }}</span>
                    @endif
                    <span class="mfg-plan__stage-btn-status mfg-plan__status mfg-plan__status--{{ $plan->review_status }}">{{ $plan->reviewStatusLabel() }}</span>
                </button>
                @endforeach
            </div>
            <span class="mfg-plan__stage-nav-hint">{{ $stageNumbers->count() }} stage{{ $stageNumbers->count() === 1 ? '' : 's' }} saved</span>
        </nav>

        <div class="mfg-plan__stage-panels">
            @foreach($stageNumbers as $stageNum)
            @php
                $plansInStage = $refinementId
                    ? $patient->visibleTreatmentPlansForStageInRefinement($refinementId, $stageNum)
                    : $patient->visibleTreatmentPlansForStageInOriginalCycle($stageNum);
                $currentStagePlan = $stagePlans->firstWhere('stage_number', $stageNum);
                $canUploadThisStage = $isActiveCtx && ($canUploadTreatmentPlan ?? false) && $patient->canAdminUploadStageTreatmentPlan($stageNum);
                $canReviewStage = $isActiveCtx && ($canReviewTreatmentPlan ?? false) && $patient->canDoctorReviewStage($stageNum);
            @endphp
            <div class="mfg-plan__stage-panel @if($stageNum === $activeStage) is-active @endif"
                 id="mfg-stage-panel-{{ $ctxKey }}-{{ $stageNum }}"
                 role="tabpanel"
                 data-mfg-stage-panel="{{ $stageNum }}"
                 data-mfg-context="{{ $ctxKey }}"
                 @if($stageNum !== $activeStage) hidden @endif>
                @include('theme.pages.partials.case-manufacture-plan-versions', [
                    'plans' => $plansInStage,
                    'patient' => $patient,
                    'navKey' => $ctxKey.'-stage-'.$stageNum,
                    'titleResolver' => fn ($plan) => $plan->stageLabel(),
                    'canReview' => $canReviewStage,
                    'canUpload' => $canUploadTreatmentPlan ?? false,
                    'canMarkManufactured' => $isActiveCtx && ($canMarkManufactured ?? false),
                    'inStagePicker' => true,
                    'enablePlanSetSwitcher' => false,
                ])

                @if($isActiveCtx && $canUploadThisStage && $currentStagePlan?->isRejected())
                <section class="mfg-plan__panel mfg-plan__panel--admin mfg-plan__panel--revision">
                    <h4 class="mfg-plan__panel-title"><i class="zmdi zmdi-refresh"></i> Submit revised plan (stage {{ $stageNum }})</h4>
                    <p class="mfg-plan__panel-desc">The rejected submission stays visible above until this revision is approved.</p>
                    <form method="post" action="{{ route('patients.treatment-plan.stage.store', $patient) }}" class="mfg-plan__form mfg-plan__form--revise">
                        @csrf
                        <input type="hidden" name="stage_number" value="{{ $stageNum }}">
                        <div class="mfg-plan__form-row">
                            <div class="mfg-plan__field mfg-plan__field--narrow">
                                <label for="mfg-revise-from-{{ $ctxKey }}-{{ $stageNum }}">Steps from</label>
                                <input type="number" id="mfg-revise-from-{{ $ctxKey }}-{{ $stageNum }}" name="step_from" min="1" max="999" value="{{ $currentStagePlan->step_from ?? 1 }}" required>
                            </div>
                            <div class="mfg-plan__field mfg-plan__field--narrow">
                                <label for="mfg-revise-to-{{ $ctxKey }}-{{ $stageNum }}">Steps to</label>
                                <input type="number" id="mfg-revise-to-{{ $ctxKey }}-{{ $stageNum }}" name="step_to" min="1" max="999" value="{{ $currentStagePlan->step_to ?? 1 }}" required>
                            </div>
                        </div>
                        <div class="mfg-plan__field">
                            <label for="mfg-revise-url-{{ $ctxKey }}-{{ $stageNum }}">Revised canvas link</label>
                            <input type="url" id="mfg-revise-url-{{ $ctxKey }}-{{ $stageNum }}" name="plan_url" placeholder="https://…" required>
                        </div>
                        <button type="submit" class="mfg-plan__btn mfg-plan__btn--primary">Submit revision for review</button>
                    </form>
                </section>
                @elseif($isActiveCtx && $canUploadTreatmentPlan && $currentStagePlan?->isPending() && $patient->hasActiveModificationFor($stageNum))
                <section class="mfg-plan__panel mfg-plan__panel--admin mfg-plan__panel--revision">
                    <h4 class="mfg-plan__panel-title"><i class="zmdi zmdi-link"></i> Upload revised plan after modification</h4>
                    <p class="mfg-plan__panel-desc">The doctor requested changes for this stage. Upload the updated canvas link for review.</p>
                    <form method="post" action="{{ route('patients.treatment-plan.stage.store', $patient) }}" class="mfg-plan__form">
                        @csrf
                        <input type="hidden" name="stage_number" value="{{ $stageNum }}">
                        <div class="mfg-plan__form-row">
                            <div class="mfg-plan__field mfg-plan__field--narrow">
                                <label for="mfg-mod-pending-from-{{ $ctxKey }}-{{ $stageNum }}">Steps from</label>
                                <input type="number" id="mfg-mod-pending-from-{{ $ctxKey }}-{{ $stageNum }}" name="step_from" min="1" max="999" value="{{ $currentStagePlan->step_from ?? 1 }}" required>
                            </div>
                            <div class="mfg-plan__field mfg-plan__field--narrow">
                                <label for="mfg-mod-pending-to-{{ $ctxKey }}-{{ $stageNum }}">Steps to</label>
                                <input type="number" id="mfg-mod-pending-to-{{ $ctxKey }}-{{ $stageNum }}" name="step_to" min="1" max="999" value="{{ $currentStagePlan->step_to ?? 1 }}" required>
                            </div>
                        </div>
                        <div class="mfg-plan__field">
                            <label for="mfg-mod-pending-url-{{ $ctxKey }}-{{ $stageNum }}">Revised canvas link</label>
                            <input type="url" id="mfg-mod-pending-url-{{ $ctxKey }}-{{ $stageNum }}" name="plan_url" placeholder="https://…" required>
                        </div>
                        <button type="submit" class="mfg-plan__btn mfg-plan__btn--primary">Submit revised plan for review</button>
                    </form>
                </section>
                @elseif($isActiveCtx && $canUploadTreatmentPlan && $currentStagePlan?->isPending())
                <p class="mfg-plan__locked-note"><i class="zmdi zmdi-lock"></i> Awaiting doctor review — upload is disabled until this stage is rejected.</p>
                @elseif($isActiveCtx && $canUploadTreatmentPlan && $currentStagePlan?->isApproved() && ! $patient->hasActiveModificationFor($stageNum))
                <p class="mfg-plan__locked-note mfg-plan__locked-note--ok"><i class="zmdi zmdi-check-circle"></i> This stage is approved. No further upload is needed until the doctor requests a modification.</p>
                @endif
            </div>
            @endforeach
        </div>

        @if($isActiveCtx && ($patient->hasCompletedManufacturing() || (($canMarkManufactured ?? false) && $patient->isReadyForManufacturedMark())))
        @include('theme.pages.partials.case-manufacture-plan-manufactured-banner', [
            'patient' => $patient,
            'canMarkManufactured' => $canMarkManufactured ?? false,
        ])
        @endif

        @if($isActiveCtx)
        @include('theme.pages.partials.case-manufacture-plan-add-stage', [
            'patient' => $patient,
            'canUploadTreatmentPlan' => $canUploadTreatmentPlan ?? false,
            'canAdd' => $canAddNewStage,
            'blockedReason' => $addStageBlockedReason,
            'suggestedStage' => $suggestedStage,
            'suggestedStepFrom' => $suggestedStepFrom,
        ])
        @endif
        @endif
    @else
        @php
            $visibleFullPlans = $ctx['visible_full_plans'] ?? collect();
            $fullPlan = $ctx['full_plan'] ?? null;
            $canUploadFull = $isActiveCtx && ($canAdminUploadFullPlan ?? false) && ($canUploadTreatmentPlan ?? false);
        @endphp
        @if($visibleFullPlans->isEmpty())
        <article class="mfg-plan__card">
            <header class="mfg-plan__card-head mfg-plan__card-head--with-switcher">
                <div class="mfg-plan__card-head-text">
                    <h4 class="mfg-plan__card-title">{{ $ctx['label'] ?? 'Treatment case plan' }}</h4>
                    <span class="mfg-plan__status mfg-plan__status--pending">No plan yet</span>
                </div>
                @include('theme.pages.partials.case-treatment-plan-context-switcher', [
                    'contexts' => $treatmentPlanContexts ?? [],
                    'defaultContextKey' => $defaultTreatmentPlanContextKey ?? 'original',
                    'selectId' => 'mfg-plan-context-select-'.$ctxKey,
                ])
            </header>
        </article>
        <div class="mfg-plan__empty">
            <i class="zmdi zmdi-assignment" aria-hidden="true"></i>
            <p>No treatment plan has been uploaded for {{ $ctx['label'] ?? 'this cycle' }}.</p>
            @if($canUploadFull)
            <span class="mfg-plan__empty-hint">Paste the canvas link from the LineUp viewer below.</span>
            @endif
        </div>
        @if($isActiveCtx && ($patient->hasCompletedManufacturing() || (($canMarkManufactured ?? false) && $patient->isReadyForManufacturedMark())))
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
            'canMarkManufactured' => $isActiveCtx && ($canMarkManufactured ?? false),
            'enablePlanSetSwitcher' => true,
            'treatmentPlanContexts' => $treatmentPlanContexts ?? [],
            'defaultTreatmentPlanContextKey' => $defaultTreatmentPlanContextKey ?? 'original',
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
        @elseif($isActiveCtx && $canUploadTreatmentPlan && $fullPlan?->isApproved() && ! $patient->hasActiveModificationFor(null))
        <p class="mfg-plan__locked-note mfg-plan__locked-note--ok"><i class="zmdi zmdi-check-circle"></i> This plan is approved. No further upload is required until the doctor requests a modification.</p>
        @endif
    @endif
</div>
