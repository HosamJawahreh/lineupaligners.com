@php
    $isDivided = $patient->isDividedStages();
    $hasWorkflowPermission = $canRequestModification ?? false;
    $canRequestNow = $patient->canRequestModificationNow();
    $canRequest = $hasWorkflowPermission && $canRequestNow;
    $awaitingPlan = $patient->hasModificationAwaitingPlan(null);
    $reviewStage = null;
@endphp

<div class="case-modification" id="case-modification-request">
    <header class="case-modification__head">
        <h3 class="case-modification__title">Request Modification</h3>
        <p class="case-modification__subtitle">
            Request plan changes after a treatment plan is uploaded and before the case is manufactured. Upload scans, photos, and notes here — view them under <strong>3D Scans &amp; Photos</strong> and the revised plan under <strong>Treatment Plan</strong>.
        </p>
    </header>

    @if(session('success') && (session('open_tab') === 'modification' || request('tab') === 'modification'))
    <div class="case-modification__notice case-modification__notice--success" role="status">
        <i class="zmdi zmdi-check-circle" aria-hidden="true"></i>
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error') && (session('open_tab') === 'modification' || request('tab') === 'modification'))
    <div class="case-modification__notice case-modification__notice--error" role="alert">
        <i class="zmdi zmdi-alert-circle" aria-hidden="true"></i>
        <p>{{ session('error') }}</p>
    </div>
    @endif

    @if($errors->any() && (session('open_tab') === 'modification' || request('tab') === 'modification' || old('notes') !== null))
    <div class="case-modification__notice case-modification__notice--error" role="alert">
        <i class="zmdi zmdi-alert-circle" aria-hidden="true"></i>
        <p>{{ $errors->first('notes') ?: $errors->first() }}</p>
    </div>
    @endif

    <div class="case-modification__layout">
        <div class="case-modification__main">
            @if($canRequest)
                @if($awaitingPlan)
                <div class="case-modification__notice case-modification__notice--info">
                    <i class="zmdi zmdi-info-outline" aria-hidden="true"></i>
                    <p>A modification is already in progress. You can still submit a new request when the current plan cycle allows it.</p>
                </div>
                @endif

                <section class="case-modification-card" aria-labelledby="case-modification-form-title">
                    <div class="case-modification-card__accent" aria-hidden="true"></div>

                    <header class="case-modification-card__head">
                        <span class="case-modification-card__icon" aria-hidden="true">
                            <i class="zmdi zmdi-refresh-sync"></i>
                        </span>
                        <div class="case-modification-card__head-text">
                            <p class="case-modification-card__kicker">Plan changes</p>
                            <h4 class="case-modification-card__title" id="case-modification-form-title">Submit modification request</h4>
                            <p class="case-modification-card__lead">
                                Notes are required. Upload revised 3D scans and photos if needed — LineUp will prepare an updated plan for your review.
                            </p>
                        </div>
                    </header>

                    <form method="post"
                          action="{{ route('patients.modifications.store', $patient) }}"
                          class="case-modification-card__form"
                          enctype="multipart/form-data"
                          data-scan-upload>
                        @csrf

                        <div class="case-modification-card__section">
                            <h5 class="case-modification-card__section-title">
                                <i class="zmdi zmdi-cloud-upload" aria-hidden="true"></i>
                                Scans &amp; photos
                            </h5>
                            <div class="case-modification-card__uploads case-modification-card__uploads--compact">
                                <div class="case-modification-card__upload-block case-modification-card__upload-block--photos">
                                    @include('theme.pages.partials.case-photos-upload', ['uploadId' => 'modification-photos'])
                                </div>
                                @include('theme.pages.partials.case-jaw-scan-fields', [
                                    'upperInputId' => 'modification-upper',
                                    'lowerInputId' => 'modification-lower',
                                ])
                            </div>
                        </div>

                        <div class="case-modification-card__section">
                            <h5 class="case-modification-card__section-title">
                                <i class="zmdi zmdi-edit" aria-hidden="true"></i>
                                Modification notes <span class="case-modification-card__required">required</span>
                            </h5>
                            <div class="case-modification-card__field">
                                <label for="modification-notes">What should change?</label>
                                <textarea id="modification-notes"
                                          name="notes"
                                          rows="5"
                                          maxlength="10000"
                                          required
                                          aria-required="true"
                                          placeholder="Describe what should change in the new treatment plan (tooth movements, attachments, staging, etc.)">{{ old('notes') }}</textarea>
                                @error('notes')
                                <span class="case-modification-card__field-error" role="alert">{{ $message }}</span>
                                @enderror
                                <span class="case-modification-card__hint">Required — explain the changes LineUp should make to the plan.</span>
                            </div>
                        </div>

                        <footer class="case-modification-card__foot">
                            <button type="submit" class="case-modification-card__submit">
                                <i class="zmdi zmdi-upload" aria-hidden="true"></i>
                                Submit modification request
                            </button>
                        </footer>
                    </form>
                </section>
            @elseif($patient->hasCompletedManufacturing())
                <div class="case-modification__notice case-modification__notice--info">
                    <i class="zmdi zmdi-lock" aria-hidden="true"></i>
                    <p>This case cycle is manufactured and complete. Modifications are closed. Use <strong>Order Refinement</strong> when the patient returns for continued treatment.</p>
                </div>
            @elseif(auth()->user()->isDoctor())
                @if($awaitingPlan)
                <div class="case-modification__notice case-modification__notice--pending">
                    <i class="zmdi zmdi-time" aria-hidden="true"></i>
                    <p>A modification is in progress. LineUp will upload a revised plan for you to review. After you approve it, the case continues toward manufacturing — refinement is only available after LineUp marks the case as manufactured.</p>
                </div>
                @elseif($hasWorkflowPermission && ! $canRequestNow && $reviewStage)
                <div class="case-modification__notice case-modification__notice--info">
                    <i class="zmdi zmdi-info-outline" aria-hidden="true"></i>
                    <p>A treatment plan is pending your approval on the <strong>Treatment Plan</strong> tab. You can request a modification here before approving when the case is eligible.</p>
                </div>
                @elseif(! $hasWorkflowPermission)
                <div class="case-modification__notice case-modification__notice--info">
                    <i class="zmdi zmdi-lock" aria-hidden="true"></i>
                    <p>Your account role does not include modification requests. Contact LineUp admin to update your permissions.</p>
                </div>
                @else
                <div class="case-modification__notice case-modification__notice--info">
                    <i class="zmdi zmdi-info-outline" aria-hidden="true"></i>
                    <p>No modification can be started right now. A plan must be uploaded and awaiting your review, or already approved.</p>
                </div>
                @endif
            @else
                @if($awaitingPlan)
                <div class="case-modification__notice case-modification__notice--pending">
                    <i class="zmdi zmdi-time" aria-hidden="true"></i>
                    <p>A modification is in progress. Upload the revised plan on the <strong>Treatment Plan</strong> tab. The assigned doctor will review it when ready.</p>
                </div>
                @elseif($canRequestNow)
                <div class="case-modification__notice case-modification__notice--info">
                    <i class="zmdi zmdi-account" aria-hidden="true"></i>
                    <p>
                        A treatment plan is uploaded and ready for modification.
                        @if($patient->doctor)
                        <strong>Dr. {{ $patient->doctor->fullName() }}</strong> can submit a request from this tab when logged in as the assigned doctor.
                        @else
                        The assigned doctor can submit a request from this tab.
                        @endif
                    </p>
                </div>
                @else
                <div class="case-modification__notice case-modification__notice--info">
                    <i class="zmdi zmdi-account" aria-hidden="true"></i>
                    <p>Only the assigned doctor can submit modification requests. When a request is active, upload a revised plan in the Treatment Plan tab.</p>
                </div>
                @endif
            @endif
        </div>

        <aside class="case-modification__aside" aria-label="Modification history">
            @include('theme.pages.partials.case-cycle-timeline-panel', [
                'cycleTimeline' => $modificationTimeline ?? ['events' => [], 'grouped' => []],
                'panelTitle' => 'Modification history',
                'panelSubtitle' => 'Requests, revised plans, and doctor reviews.',
                'emptyTitle' => 'No modifications yet',
                'emptyMessage' => 'Submitted requests and plan updates will appear here.',
                'timelineIdPrefix' => 'mod-history',
            ])
        </aside>
    </div>
</div>
