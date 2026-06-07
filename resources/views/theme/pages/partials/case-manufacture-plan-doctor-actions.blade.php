@php
    $showReview = ($canReview ?? false) && $plan->isPending() && $plan->is_current && ! ($isHistorical ?? false);
    $showModification = ! empty($canRequestModificationOnStage)
        && $plan->isPending()
        && $plan->is_current
        && ! ($isHistorical ?? false)
        && isset($patient);
@endphp

@if($showReview || $showModification)
<section class="mfg-plan__doctor-actions" aria-label="Doctor actions for this stage">
    @if($showReview)
    <form method="post"
          action="{{ route('patients.treatment-plan.review', $plan->patient) }}"
          class="mfg-plan__review-form mfg-plan__review-form--prominent"
          data-mfg-review-form>
        @csrf
        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
        <div class="mfg-plan__doctor-actions-head">
            <div>
                <p class="mfg-plan__doctor-actions-kicker">Your decision required</p>
                <p class="mfg-plan__doctor-actions-lead">Review the plan below, then approve for manufacture or reject with feedback for LineUp.</p>
            </div>
            <div class="mfg-plan__review-actions mfg-plan__review-actions--prominent">
                <button type="submit" name="decision" value="approved" class="mfg-plan__btn mfg-plan__btn--approve mfg-plan__btn--lg">
                    <i class="zmdi zmdi-check"></i> Approve for manufacture
                </button>
                <button type="submit" name="decision" value="rejected" class="mfg-plan__btn mfg-plan__btn--reject mfg-plan__btn--lg" data-requires-comment>
                    <i class="zmdi zmdi-close"></i> Reject
                </button>
            </div>
        </div>
        <div class="mfg-plan__field mfg-plan__field--comment">
            <label for="mfg-comment-{{ $plan->id }}">Comment <span class="mfg-plan__optional">(required if rejecting)</span></label>
            <textarea id="mfg-comment-{{ $plan->id }}" name="comment" rows="2" maxlength="5000" placeholder="Notes for LineUp admin if changes are needed…">{{ old('comment') }}</textarea>
        </div>
    </form>
    @endif

    @if($showModification)
    @include('theme.pages.partials.case-modification-inline', [
        'patient' => $patient,
        'stageNumber' => $plan->stage_number,
    ])
    @endif
</section>
@endif
