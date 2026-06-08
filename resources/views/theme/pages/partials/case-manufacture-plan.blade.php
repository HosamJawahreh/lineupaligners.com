@php
    $isDivided = $patient->isDividedStages();
    $fullPlan = $fullTreatmentPlan ?? null;
    $visibleFullPlans = $visibleFullTreatmentPlans ?? collect();
    $canUploadFull = ($canAdminUploadFullPlan ?? false) && ($canUploadTreatmentPlan ?? false);
    $stagePlans = $stageTreatmentPlans ?? collect();
    $stageNumbers = $treatmentPlanStageNumbers ?? collect();
@endphp

<div class="mfg-plan" id="case-manufacture-plan">
    @if($patient->hasActiveRefinement())
    <div class="mfg-plan__mod-banner mfg-plan__mod-banner--refinement" role="status">
        <i class="zmdi zmdi-swap-vertical" aria-hidden="true"></i>
        <div>
            <strong>Refinement case in progress</strong>
            <p>The doctor ordered a refinement (returning patient). Treat this as a <strong>new case cycle</strong> — upload new plan link(s) below. Previous manufactured plans and scans remain in Case History and 3D Scans &amp; Photos.</p>
        </div>
    </div>
    @elseif($patient->hasActiveModificationForAny())
    <div class="mfg-plan__mod-banner" role="status">
        <i class="zmdi zmdi-refresh-sync" aria-hidden="true"></i>
        <div>
            <strong>Modification requested</strong>
            <p>The doctor uploaded new 3D scans and notes. Upload a revised treatment plan canvas link below for review. This updates the <strong>same plan version</strong> — modification data stays in Request Modification.</p>
        </div>
    </div>
    @endif

    <header class="mfg-plan__head">
        <div class="mfg-plan__head-text">
            <h3 class="mfg-plan__title @if(!$isDivided) mfg-plan__title--solo @endif">Treatment Plan</h3>
            @if($isDivided)
            <p class="mfg-plan__subtitle">
                Each stage covers a step range (from–to) with its own viewer link. Every stage follows a full approve / reject cycle, like a full case.
            </p>
            @endif
        </div>
        <span class="mfg-plan__type-badge">{{ $patient->caseTypeLabel() }}</span>
    </header>

    @if($isDivided)
        @php
            $suggestedStage = (int) ($stageNumbers->max() + 1);
            if ($stageNumbers->isEmpty()) {
                $suggestedStage = 1;
            }
            $suggestedStepFrom = (int) (old('step_from', $stagePlans->max('step_to') ? $stagePlans->max('step_to') + 1 : 1));
            $canAddNewStage = ($canUploadTreatmentPlan ?? false) && $patient->canAdminAddNewDividedStage();
            $reviewStage = $patient->doctorReviewStageNumber();
            $addStageBlockedReason = null;
            if (($canUploadTreatmentPlan ?? false) && ! $canAddNewStage) {
                $addStageBlockedReason = $reviewStage !== null
                    ? 'Stage '.$reviewStage.' is awaiting doctor approval. Add the next stage only after it is approved.'
                    : ($patient->hasActiveModificationForAny()
                        ? 'A modification is in progress. Upload the revised plan for the current stage before adding a new one.'
                        : 'Complete the current stage before adding another.');
            }
        @endphp

        @if($stageNumbers->isEmpty())
        <div class="mfg-plan__empty">
            <i class="zmdi zmdi-assignment-o" aria-hidden="true"></i>
            <p>No stage plans uploaded yet.</p>
            @if($canUploadTreatmentPlan ?? false)
            <span class="mfg-plan__empty-hint">Start with stage 1 — define the step range and canvas link below.</span>
            @endif
        </div>

        @include('theme.pages.partials.case-manufacture-plan-add-stage', [
            'patient' => $patient,
            'canUploadTreatmentPlan' => $canUploadTreatmentPlan ?? false,
            'canAdd' => $canAddNewStage,
            'blockedReason' => $addStageBlockedReason,
            'suggestedStage' => $suggestedStage,
            'suggestedStepFrom' => $suggestedStepFrom,
        ])
        @else
        @php
            $reviewStage = $patient->doctorReviewStageNumber();
            $defaultStage = $reviewStage ?? (int) ($patient->currentDividedStageNumber() ?? $stageNumbers->max());
            $activeStage = (int) (session('mfg_active_stage') ?? $defaultStage);
            if (! $stageNumbers->contains($activeStage)) {
                $activeStage = (int) $stageNumbers->first();
            }
        @endphp
        <nav class="mfg-plan__stage-nav" aria-label="Treatment plan stages" data-mfg-stage-nav>
            <span class="mfg-plan__stage-nav-label">View stage</span>
            <div class="mfg-plan__stage-nav-buttons" role="tablist">
                @foreach($stagePlans as $plan)
                <button type="button"
                        role="tab"
                        class="mfg-plan__stage-btn @if($plan->stage_number === $activeStage) is-active @endif"
                        data-mfg-stage-btn
                        data-stage="{{ $plan->stage_number }}"
                        aria-selected="{{ $plan->stage_number === $activeStage ? 'true' : 'false' }}"
                        aria-controls="mfg-stage-panel-{{ $plan->stage_number }}"
                        id="mfg-stage-tab-{{ $plan->stage_number }}">
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
                $plansInStage = $patient->visibleTreatmentPlansForStage($stageNum);
                $currentStagePlan = $stagePlans->firstWhere('stage_number', $stageNum);
                $canUploadThisStage = ($canUploadTreatmentPlan ?? false) && $patient->canAdminUploadStageTreatmentPlan($stageNum);
            @endphp
            <div class="mfg-plan__stage-panel @if($stageNum === $activeStage) is-active @endif"
                 id="mfg-stage-panel-{{ $stageNum }}"
                 role="tabpanel"
                 aria-labelledby="mfg-stage-tab-{{ $stageNum }}"
                 data-mfg-stage-panel="{{ $stageNum }}"
                 @if($stageNum !== $activeStage) hidden @endif>
                @include('theme.pages.partials.case-manufacture-plan-versions', [
                    'plans' => $plansInStage,
                    'patient' => $patient,
                    'navKey' => 'stage-'.$stageNum,
                    'titleResolver' => fn ($plan) => $plan->stageLabel(),
                    'canReview' => ($canReviewTreatmentPlan ?? false) && $patient->canDoctorReviewStage($stageNum),
                    'canUpload' => $canUploadTreatmentPlan ?? false,
                    'canMarkManufactured' => $canMarkManufactured ?? false,
                    'inStagePicker' => true,
                ])

                @if($canUploadThisStage && $currentStagePlan?->isRejected())
                <section class="mfg-plan__panel mfg-plan__panel--admin mfg-plan__panel--revision">
                    <h4 class="mfg-plan__panel-title"><i class="zmdi zmdi-refresh"></i> Submit revised plan (stage {{ $stageNum }})</h4>
                    <p class="mfg-plan__panel-desc">The rejected submission stays visible above until this revision is approved.</p>
                    <form method="post" action="{{ route('patients.treatment-plan.stage.store', $patient) }}" class="mfg-plan__form mfg-plan__form--revise">
                        @csrf
                        <input type="hidden" name="stage_number" value="{{ $stageNum }}">
                        <div class="mfg-plan__form-row">
                            <div class="mfg-plan__field mfg-plan__field--narrow">
                                <label for="mfg-revise-from-{{ $stageNum }}">Steps from</label>
                                <input type="number" id="mfg-revise-from-{{ $stageNum }}" name="step_from" min="1" max="999" value="{{ $currentStagePlan->step_from ?? 1 }}" required>
                            </div>
                            <div class="mfg-plan__field mfg-plan__field--narrow">
                                <label for="mfg-revise-to-{{ $stageNum }}">Steps to</label>
                                <input type="number" id="mfg-revise-to-{{ $stageNum }}" name="step_to" min="1" max="999" value="{{ $currentStagePlan->step_to ?? 1 }}" required>
                            </div>
                        </div>
                        <div class="mfg-plan__field">
                            <label for="mfg-revise-url-{{ $stageNum }}">Revised canvas link</label>
                            <input type="url" id="mfg-revise-url-{{ $stageNum }}" name="plan_url" placeholder="https://…" required>
                        </div>
                        <button type="submit" class="mfg-plan__btn mfg-plan__btn--primary">Submit revision for review</button>
                    </form>
                </section>
                @elseif($canUploadTreatmentPlan && $currentStagePlan?->isPending() && $patient->hasActiveModificationFor($stageNum))
                <section class="mfg-plan__panel mfg-plan__panel--admin mfg-plan__panel--revision">
                    <h4 class="mfg-plan__panel-title"><i class="zmdi zmdi-link"></i> Upload revised plan after modification</h4>
                    <p class="mfg-plan__panel-desc">The doctor requested changes for this stage. Upload the updated canvas link for review.</p>
                    <form method="post" action="{{ route('patients.treatment-plan.stage.store', $patient) }}" class="mfg-plan__form">
                        @csrf
                        <input type="hidden" name="stage_number" value="{{ $stageNum }}">
                        <div class="mfg-plan__form-row">
                            <div class="mfg-plan__field mfg-plan__field--narrow">
                                <label for="mfg-mod-pending-from-{{ $stageNum }}">Steps from</label>
                                <input type="number" id="mfg-mod-pending-from-{{ $stageNum }}" name="step_from" min="1" max="999" value="{{ $currentStagePlan->step_from ?? 1 }}" required>
                            </div>
                            <div class="mfg-plan__field mfg-plan__field--narrow">
                                <label for="mfg-mod-pending-to-{{ $stageNum }}">Steps to</label>
                                <input type="number" id="mfg-mod-pending-to-{{ $stageNum }}" name="step_to" min="1" max="999" value="{{ $currentStagePlan->step_to ?? 1 }}" required>
                            </div>
                        </div>
                        <div class="mfg-plan__field">
                            <label for="mfg-mod-pending-url-{{ $stageNum }}">Revised canvas link</label>
                            <input type="url" id="mfg-mod-pending-url-{{ $stageNum }}" name="plan_url" placeholder="https://…" required>
                        </div>
                        <button type="submit" class="mfg-plan__btn mfg-plan__btn--primary">Submit revised plan for review</button>
                    </form>
                </section>
                @elseif($canUploadTreatmentPlan && $currentStagePlan?->isPending())
                <p class="mfg-plan__locked-note"><i class="zmdi zmdi-lock"></i> Awaiting doctor review — upload is disabled until this stage is rejected.</p>
                @elseif($canUploadTreatmentPlan && $currentStagePlan?->isApproved() && ! $patient->hasActiveModificationFor($stageNum))
                <p class="mfg-plan__locked-note mfg-plan__locked-note--ok"><i class="zmdi zmdi-check-circle"></i> This stage is approved. No further upload is needed until the doctor requests a modification.</p>
                @elseif($canUploadTreatmentPlan && $currentStagePlan?->isApproved() && $patient->hasActiveModificationFor($stageNum))
                <section class="mfg-plan__panel mfg-plan__panel--admin mfg-plan__panel--revision">
                    <h4 class="mfg-plan__panel-title"><i class="zmdi zmdi-link"></i> Upload plan after modification</h4>
                    <p class="mfg-plan__panel-desc">The doctor submitted new scans for this stage. Upload the revised canvas link for review.</p>
                    <form method="post" action="{{ route('patients.treatment-plan.stage.store', $patient) }}" class="mfg-plan__form">
                        @csrf
                        <input type="hidden" name="stage_number" value="{{ $stageNum }}">
                        <div class="mfg-plan__form-row">
                            <div class="mfg-plan__field mfg-plan__field--narrow">
                                <label for="mfg-mod-from-{{ $stageNum }}">Steps from</label>
                                <input type="number" id="mfg-mod-from-{{ $stageNum }}" name="step_from" min="1" max="999" value="{{ $currentStagePlan->step_from ?? 1 }}" required>
                            </div>
                            <div class="mfg-plan__field mfg-plan__field--narrow">
                                <label for="mfg-mod-to-{{ $stageNum }}">Steps to</label>
                                <input type="number" id="mfg-mod-to-{{ $stageNum }}" name="step_to" min="1" max="999" value="{{ $currentStagePlan->step_to ?? 1 }}" required>
                            </div>
                        </div>
                        <div class="mfg-plan__field">
                            <label for="mfg-mod-url-{{ $stageNum }}">Revised canvas link</label>
                            <input type="url" id="mfg-mod-url-{{ $stageNum }}" name="plan_url" placeholder="https://…" required>
                        </div>
                        <button type="submit" class="mfg-plan__btn mfg-plan__btn--primary">Submit revised plan for review</button>
                    </form>
                </section>
                @endif
            </div>
            @endforeach
        </div>

        @if($patient->hasCompletedManufacturing() || (($canMarkManufactured ?? false) && $patient->isReadyForManufacturedMark()))
        @include('theme.pages.partials.case-manufacture-plan-manufactured-banner', [
            'patient' => $patient,
            'canMarkManufactured' => $canMarkManufactured ?? false,
        ])
        @endif

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
        @if($visibleFullPlans->isEmpty())
        <div class="mfg-plan__empty">
            <i class="zmdi zmdi-assignment" aria-hidden="true"></i>
            <p>No treatment plan has been uploaded for this case.</p>
            @if($canUploadFull)
            <span class="mfg-plan__empty-hint">Paste the canvas link from the LineUp viewer below.</span>
            @else
            <p class="mfg-plan__canvas-desc">LineUp admin submits the treatment plan canvas link from the viewer. The doctor approves or rejects before manufacturing proceeds.</p>
            @endif
        </div>
        @if($patient->hasCompletedManufacturing() || (($canMarkManufactured ?? false) && $patient->isReadyForManufacturedMark()))
        @include('theme.pages.partials.case-manufacture-plan-manufactured-banner', [
            'patient' => $patient,
            'canMarkManufactured' => $canMarkManufactured ?? false,
        ])
        @endif
        @else
        @if($visibleFullPlans->count() > 1)
        <p class="mfg-plan__history-note m-b-15">Switch between versions below. The latest version is selected by default.</p>
        @endif
        @include('theme.pages.partials.case-manufacture-plan-versions', [
            'plans' => $visibleFullPlans,
            'patient' => $patient,
            'navKey' => 'full',
            'canReview' => $canReviewTreatmentPlan ?? false,
            'canUpload' => $canUploadTreatmentPlan ?? false,
            'canMarkManufactured' => $canMarkManufactured ?? false,
        ])
        @endif

        @php
            $canSubmitFullPlan = $canUploadFull && (
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
            @if($fullPlan && $fullPlan->isRejected())
            <p class="mfg-plan__panel-desc">The rejected submission stays visible above until this revision is approved.</p>
            @elseif($fullPlan && $patient->hasActiveModificationFor(null) && $fullPlan->isPending())
            <p class="mfg-plan__panel-desc">The doctor requested changes before approving this plan. Upload the updated canvas link for review.</p>
            @elseif($fullPlan && $patient->hasActiveModificationFor(null))
            <p class="mfg-plan__panel-desc">The doctor submitted new 3D scans. Upload the revised canvas link for review.</p>
            @endif
            <form method="post" action="{{ route('patients.treatment-plan.store', $patient) }}" class="mfg-plan__form">
                @csrf
                <div class="mfg-plan__field">
                    <label for="mfg-full-url">Treatment plan canvas link</label>
                    <input type="url" id="mfg-full-url" name="plan_url" value="{{ old('plan_url') }}" placeholder="https://viewer.lineup.com/…" required>
                    <span class="mfg-plan__hint">Use the share link from the LineUp treatment plan viewer (canvas).</span>
                    <p class="mfg-plan__canvas-desc">LineUp admin submits the treatment plan canvas link from the viewer. The doctor approves or rejects before manufacturing proceeds.</p>
                </div>
                <button type="submit" class="mfg-plan__btn mfg-plan__btn--primary">
                    {{ $fullPlan ? 'Submit revision for review' : 'Submit plan for review' }}
                </button>
            </form>
        </section>
        @elseif($canUploadTreatmentPlan && $fullPlan?->isPending())
        <p class="mfg-plan__locked-note"><i class="zmdi zmdi-lock"></i> Awaiting doctor review — upload is disabled until the plan is rejected.</p>
        @elseif($canUploadTreatmentPlan && $fullPlan?->isApproved() && ! $patient->hasActiveModificationFor(null))
        <p class="mfg-plan__locked-note mfg-plan__locked-note--ok"><i class="zmdi zmdi-check-circle"></i> This plan is approved. No further upload is required until the doctor requests a modification.</p>
        @endif
    @endif

    @if(($canReviewTreatmentPlan ?? false) && !($canUploadTreatmentPlan ?? false))
    <p class="mfg-plan__role-note"><i class="zmdi zmdi-info-outline"></i>
        @if($isDivided && $stageNumbers->isNotEmpty())
            Stages are reviewed in order. Approve or reject the current stage only. Rejection requires a comment for LineUp admin.
        @else
            Review the plan below. Rejection requires a comment for LineUp admin.
        @endif
    </p>
    @endif
</div>
