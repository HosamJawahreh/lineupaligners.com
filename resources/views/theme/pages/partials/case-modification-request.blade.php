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
@endphp

<div class="case-modification" id="case-modification-request">
    <header class="case-modification__head">
        <h3 class="case-modification__title">Request Modification</h3>
        <p class="case-modification__subtitle">
            Request changes by uploading revised 3D scans and notes. For divided-stage cases you can do this on the current pending stage before approving, or on an approved stage to start a new cycle. LineUp will submit an updated plan for your review.
        </p>
    </header>

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
                    <p class="case-modification-card__lead">Upload revised 3D scans and notes. LineUp will prepare an updated plan for your review.</p>
                </div>
            </header>

            <form method="post"
                  action="{{ route('patients.modifications.store', $patient) }}"
                  class="case-modification-card__form"
                  enctype="multipart/form-data"
                  data-scan-upload>
                @csrf

                @if($isDivided)
                <div class="case-modification-card__section">
                    <h5 class="case-modification-card__section-title">
                        <i class="zmdi zmdi-view-week" aria-hidden="true"></i>
                        Stage
                    </h5>
                    <div class="case-modification-card__field">
                        <label for="modification-stage">Stage to modify</label>
                        <div class="case-modification-card__select-wrap">
                            <span class="case-modification-card__select-icon" aria-hidden="true"><i class="zmdi zmdi-layers"></i></span>
                            <select id="modification-stage" name="stage_number" required>
                                @foreach($eligibleStages as $stageNum)
                                <option value="{{ $stageNum }}" @selected(old('stage_number') == $stageNum)>
                                    Stage {{ $stageNum }} (ready for modification)
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <span class="case-modification-card__hint">Current pending stages and approved stages (with no modification in progress) appear here.</span>
                    </div>
                </div>
                @endif

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
                            <label for="modification-upper">Upper jaw 3D file</label>
                            <div class="case-modification-card__file-wrap">
                                <span class="case-modification-card__file-icon" aria-hidden="true"><i class="zmdi zmdi-file"></i></span>
                                <input type="file" id="modification-upper" name="upper_jaw_scan" accept=".stl,.obj,.ply">
                            </div>
                            <span class="case-modification-card__hint">STL, OBJ, or PLY — optional if lower jaw is provided.</span>
                        </div>
                        <div class="case-modification-card__upload-block">
                            <label for="modification-lower">Lower jaw 3D file</label>
                            <div class="case-modification-card__file-wrap">
                                <span class="case-modification-card__file-icon" aria-hidden="true"><i class="zmdi zmdi-file"></i></span>
                                <input type="file" id="modification-lower" name="lower_jaw_scan" accept=".stl,.obj,.ply">
                            </div>
                            <span class="case-modification-card__hint">Upload at least one jaw model with your request.</span>
                        </div>
                    </div>
                </div>

                <div class="case-modification-card__section">
                    <h5 class="case-modification-card__section-title">
                        <i class="zmdi zmdi-edit" aria-hidden="true"></i>
                        Modification notes
                    </h5>
                    <div class="case-modification-card__field">
                        <label for="modification-notes">What should change?</label>
                        <textarea id="modification-notes"
                                  name="notes"
                                  rows="5"
                                  required
                                  minlength="10"
                                  maxlength="10000"
                                  placeholder="Describe what should change in the new treatment plan (tooth movements, attachments, staging, etc.)">{{ old('notes') }}</textarea>
                        <span class="case-modification-card__hint">Minimum 10 characters — be specific so LineUp can revise the plan accurately.</span>
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
    @elseif($patient->isManufactured())
        <div class="case-modification__notice case-modification__notice--info">
            <i class="zmdi zmdi-lock" aria-hidden="true"></i>
            <p>This case cycle is manufactured and complete. Modifications are closed. Use <strong>Order Refinement</strong> when the patient returns for continued treatment.</p>
        </div>
    @elseif(auth()->user()->isDoctor())
        @if($awaitingPlan)
        <div class="case-modification__notice case-modification__notice--pending">
            <i class="zmdi zmdi-time" aria-hidden="true"></i>
            <p>A modification cycle is in progress{{ $isDivided ? ' for one or more stages' : '' }}. LineUp will upload a revised plan for you to review. After you approve it, this modification cycle ends and you may start a new one here.</p>
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
            <p>No stage is ready for modification right now. Approve the current stage on the Treatment Plan tab first.</p>
        </div>
        @endif
    @else
        <div class="case-modification__notice case-modification__notice--info">
            <i class="zmdi zmdi-account" aria-hidden="true"></i>
            <p>Only the assigned doctor can submit modification requests. When a request is active, upload a revised plan in the Treatment Plan tab.</p>
        </div>
    @endif
</div>
