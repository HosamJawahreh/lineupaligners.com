@php
    $isDivided = $patient->isDividedStages();
    $eligibleStages = $patient->modificationEligibleStageNumbers();
    $hasWorkflowPermission = $canRequestModification ?? false;
    $canRequestNow = $patient->canRequestModificationNow();
    $canRequest = $hasWorkflowPermission && $canRequestNow;
    $awaitingPlan = $isDivided
        ? $patient->hasActiveModificationForAny()
        : $patient->hasModificationAwaitingPlan(null);
    $reviewStage = $isDivided ? $patient->doctorReviewStageNumber() : null;
    $autoStage = $isDivided
        ? ($reviewStage && $eligibleStages->contains($reviewStage) ? $reviewStage : $eligibleStages->first())
        : null;
@endphp

<div class="case-modification" id="case-modification-request">
    <header class="case-modification__head">
        <h3 class="case-modification__title">Request Modification</h3>
        <p class="case-modification__subtitle">
            Request plan changes after a treatment plan is uploaded and before the case is manufactured. Upload scans, photos, and notes here — view them under <strong>3D Scans &amp; Photos</strong> and the revised plan under <strong>Treatment Plan</strong>.
        </p>
    </header>

    <div class="case-modification__layout">
        <div class="case-modification__main">
            @if($canRequest)
                @if($isDivided && $awaitingPlan && $eligibleStages->isNotEmpty())
                <div class="case-modification__notice case-modification__notice--info">
                    <i class="zmdi zmdi-info-outline" aria-hidden="true"></i>
                    <p>Some stages still have a modification in progress. You can submit a new request below for any stage that is ready.</p>
                </div>
                @endif
                @if(! $isDivided || $eligibleStages->isNotEmpty())
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
                                @if($isDivided && $autoStage)
                                    Modifying <strong>stage {{ $autoStage }}</strong>. Notes are required; scans and photos are optional.
                                @else
                                    Notes are required. Upload revised 3D scans and photos if needed — LineUp will prepare an updated plan for your review.
                                @endif
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
                            <div class="case-modification-card__uploads">
                                <div class="case-modification-card__upload-block case-modification-card__upload-block--photos">
                                    @include('theme.pages.partials.case-photos-upload', ['uploadId' => 'modification-photos'])
                                </div>
                                <div class="case-modification-card__upload-block">
                                    <label for="modification-upper">Upper jaw 3D file <span class="case-modification-card__optional">optional</span></label>
                                    <div class="case-modification-card__file-wrap">
                                        <span class="case-modification-card__file-icon" aria-hidden="true"><i class="zmdi zmdi-file"></i></span>
                                        <input type="file" id="modification-upper" name="upper_jaw_scan" accept=".stl,.obj,.ply,.zip">
                                    </div>
                                    <span class="case-modification-card__hint">STL, OBJ, or PLY — upload when you have a new upper scan.</span>
                                </div>
                                <div class="case-modification-card__upload-block">
                                    <label for="modification-lower">Lower jaw 3D file <span class="case-modification-card__optional">optional</span></label>
                                    <div class="case-modification-card__file-wrap">
                                        <span class="case-modification-card__file-icon" aria-hidden="true"><i class="zmdi zmdi-file"></i></span>
                                        <input type="file" id="modification-lower" name="lower_jaw_scan" accept=".stl,.obj,.ply,.zip">
                                    </div>
                                    <span class="case-modification-card__hint">STL, OBJ, or PLY — upload when you have a new lower scan.</span>
                                </div>
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
                                          placeholder="Describe what should change in the new treatment plan (tooth movements, attachments, staging, etc.)">{{ old('notes') }}</textarea>
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
                @elseif($isDivided)
                <div class="case-modification__notice case-modification__notice--info">
                    <i class="zmdi zmdi-info-outline" aria-hidden="true"></i>
                    <p>No stage is ready for a new modification. Review the current stage in Treatment Plan, or wait until LineUp finishes a modification already in progress.</p>
                </div>
                @endif
            @elseif($patient->hasCompletedManufacturing())
                <div class="case-modification__notice case-modification__notice--info">
                    <i class="zmdi zmdi-lock" aria-hidden="true"></i>
                    <p>This case cycle is manufactured and complete. Modifications are closed. Use <strong>Order Refinement</strong> when the patient returns for continued treatment.</p>
                </div>
            @elseif(auth()->user()->isDoctor())
                @if($awaitingPlan)
                <div class="case-modification__notice case-modification__notice--pending">
                    <i class="zmdi zmdi-time" aria-hidden="true"></i>
                    <p>A modification is in progress{{ $isDivided ? ' for one or more stages' : '' }}. LineUp will upload a revised plan for you to review. After you approve it, the case continues toward manufacturing — refinement is only available after LineUp marks the case as manufactured.</p>
                </div>
                @elseif($hasWorkflowPermission && ! $canRequestNow && $reviewStage)
                <div class="case-modification__notice case-modification__notice--info">
                    <i class="zmdi zmdi-info-outline" aria-hidden="true"></i>
                    <p>Stage {{ $reviewStage }} is pending your approval on the <strong>Treatment Plan</strong> tab. You can also request a modification here before approving.</p>
                </div>
                @elseif(! $hasWorkflowPermission)
                <div class="case-modification__notice case-modification__notice--info">
                    <i class="zmdi zmdi-lock" aria-hidden="true"></i>
                    <p>Your account role does not include modification requests. Contact LineUp admin to update your permissions.</p>
                </div>
                @else
                <div class="case-modification__notice case-modification__notice--info">
                    <i class="zmdi zmdi-info-outline" aria-hidden="true"></i>
                    <p>
                        @if($isDivided)
                            No stage is ready for modification right now. Approve the current stage on the Treatment Plan tab first.
                        @else
                            No modification can be started right now. A plan must be uploaded and awaiting your review, or already approved.
                        @endif
                    </p>
                </div>
                @endif
            @else
                <div class="case-modification__notice case-modification__notice--info">
                    <i class="zmdi zmdi-account" aria-hidden="true"></i>
                    <p>Only the assigned doctor can submit modification requests. When a request is active, upload a revised plan in the Treatment Plan tab.</p>
                </div>
            @endif
        </div>

        <aside class="case-modification__aside" aria-label="Modification history">
            @include('theme.pages.partials.case-modification-records', [
                'patient' => $patient,
                'modificationRecords' => $modificationRecords ?? collect(),
            ])
        </aside>
    </div>
</div>
