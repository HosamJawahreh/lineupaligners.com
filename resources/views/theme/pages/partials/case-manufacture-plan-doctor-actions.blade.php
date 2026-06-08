@php
    $showReview = ($canReview ?? false) && $plan->isPending() && $plan->is_current && ! ($isHistorical ?? false);
@endphp

@if($showReview)
<section class="mfg-plan__doctor-actions" aria-label="Doctor actions for this stage">
    <form method="post"
          action="{{ route('patients.treatment-plan.review', $plan->patient) }}"
          class="mfg-plan__review-form mfg-plan__review-form--prominent"
          data-mfg-review-form>
        @csrf
        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
        <div class="mfg-plan__doctor-actions-head">
            <div>
                <p class="mfg-plan__doctor-actions-kicker">Your decision required</p>
                <p class="mfg-plan__doctor-actions-lead">Review the plan below, then approve for manufacture or request changes on the <strong>Request Modification</strong> tab.</p>
            </div>
            <div class="mfg-plan__review-actions mfg-plan__review-actions--prominent">
                <button type="submit" name="decision" value="approved" class="mfg-plan__btn mfg-plan__btn--approve mfg-plan__btn--lg">
                    <i class="zmdi zmdi-check"></i> Approve for manufacture
                </button>
                <button type="button"
                        class="mfg-plan__btn mfg-plan__btn--reject mfg-plan__btn--lg"
                        data-case-tab-switch="modification">
                    <i class="zmdi zmdi-refresh-sync"></i> Order Modification
                </button>
            </div>
        </div>
        <p class="mfg-plan__doctor-actions-note">
            <i class="zmdi zmdi-info-outline" aria-hidden="true"></i>
            To request plan changes, open <strong>Request Modification</strong> — add notes there and upload new scans or photos if needed.
        </p>
    </form>
</section>
@endif
