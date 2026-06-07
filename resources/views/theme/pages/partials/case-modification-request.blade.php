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
        <form method="post"
              action="{{ route('patients.modifications.store', $patient) }}"
              class="case-modification__form"
              enctype="multipart/form-data"
              data-scan-upload>
            @csrf

            @if($isDivided)
            <div class="case-modification__field">
                <label for="modification-stage">Stage to modify</label>
                <select id="modification-stage" name="stage_number" required>
                    @foreach($eligibleStages as $stageNum)
                    <option value="{{ $stageNum }}" @selected(old('stage_number') == $stageNum)>
                        Stage {{ $stageNum }} (ready for modification)
                    </option>
                    @endforeach
                </select>
                <span class="case-modification__hint">Current pending stages and approved stages (with no modification in progress) appear here.</span>
            </div>
            @endif

            <div class="case-modification__uploads">
                @include('theme.pages.partials.case-photos-upload', ['uploadId' => 'modification-photos'])
                <div class="case-modification__field">
                    <label for="modification-upper">Upper jaw 3D file</label>
                    <input type="file" id="modification-upper" name="upper_jaw_scan" accept=".stl,.obj,.ply">
                    <span class="case-modification__hint">STL, OBJ, or PLY — optional if lower jaw is provided.</span>
                </div>
                <div class="case-modification__field">
                    <label for="modification-lower">Lower jaw 3D file</label>
                    <input type="file" id="modification-lower" name="lower_jaw_scan" accept=".stl,.obj,.ply">
                    <span class="case-modification__hint">Upload at least one jaw model with your request.</span>
                </div>
            </div>

            <div class="case-modification__field">
                <label for="modification-notes">Modification notes</label>
                <textarea id="modification-notes"
                          name="notes"
                          rows="6"
                          required
                          minlength="10"
                          maxlength="10000"
                          placeholder="Describe what should change in the new treatment plan (tooth movements, attachments, staging, etc.)">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="case-modification__submit">
                <i class="zmdi zmdi-upload" aria-hidden="true"></i>
                Submit modification request
            </button>
        </form>
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
            <p>Stage {{ $reviewStage }} is ready on the <strong>Treatment Plan</strong> tab — approve it there or request a modification before approving.</p>
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
