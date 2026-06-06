@php
    $statusClass = match ($plan->review_status) {
        'approved' => 'is-approved',
        'rejected' => 'is-rejected',
        default => 'is-pending',
    };
    $isHistorical = ! empty($isHistorical);
@endphp

<article class="mfg-plan__card {{ $statusClass }} @if(!empty($inStagePicker)) mfg-plan__card--in-picker @endif @if($isHistorical) mfg-plan__card--historical @endif">
    @if($isHistorical)
    <p class="mfg-plan__history-label">Previous submission · Version {{ $plan->version }}</p>
    @endif
    <header class="mfg-plan__card-head">
        <div>
            <h4 class="mfg-plan__card-title">{{ $title }}</h4>
            @if($plan->stage_number)
            <p class="mfg-plan__card-cycle">Full approve / reject cycle for this stage</p>
            @endif
            <span class="mfg-plan__status mfg-plan__status--{{ $plan->review_status }}">{{ $plan->reviewStatusLabel() }}</span>
            @if($plan->version > 1)
            <span class="mfg-plan__version">Version {{ $plan->version }}</span>
            @endif
        </div>
    </header>

    <div class="mfg-plan__canvas-wrap">
        <iframe
            src="{{ $plan->plan_url }}"
            title="{{ $title }} treatment plan"
            class="mfg-plan__canvas"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen></iframe>
    </div>
    @if(! $plan->stage_number)
    <p class="mfg-plan__canvas-desc">LineUp admin submits the treatment plan canvas link from the viewer. The doctor approves or rejects before manufacturing proceeds.</p>
    @endif
    @if($plan->is_current && ! $isHistorical && isset($patient))
    @include('theme.pages.partials.case-manufacture-plan-manufactured-banner', [
        'patient' => $patient,
        'canMarkManufactured' => $canMarkManufactured ?? false,
    ])
    @endif

    <footer class="mfg-plan__card-foot">
        <dl class="mfg-plan__meta">
            @if($plan->hasStepRange())
            <div>
                <dt>Step range</dt>
                <dd>{{ $plan->stepRangeLabel() }}</dd>
            </div>
            @endif
            @if($plan->stage_number)
            <div>
                <dt>Stage</dt>
                <dd>#{{ $plan->stage_number }}</dd>
            </div>
            @endif
            <div>
                <dt>Uploaded</dt>
                <dd>{{ $plan->created_at?->format('M j, Y g:i A') ?? '—' }}</dd>
            </div>
            @if($plan->reviewed_at)
            <div>
                <dt>Reviewed</dt>
                <dd>{{ $plan->reviewed_at->format('M j, Y g:i A') }}</dd>
            </div>
            @endif
        </dl>

        @if($plan->review_comment)
        <div class="mfg-plan__comment mfg-plan__comment--{{ $plan->review_status }}">
            <strong>Doctor feedback</strong>
            <p>{{ $plan->review_comment }}</p>
        </div>
        @endif

        @if($canReview && $plan->isPending() && $plan->is_current && ! $isHistorical)
        <form method="post"
              action="{{ route('patients.treatment-plan.review', $plan->patient) }}"
              class="mfg-plan__review-form"
              data-mfg-review-form>
            @csrf
            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
            <div class="mfg-plan__field">
                <label for="mfg-comment-{{ $plan->id }}">Comment <span class="mfg-plan__optional">(required if rejecting)</span></label>
                <textarea id="mfg-comment-{{ $plan->id }}" name="comment" rows="3" maxlength="5000" placeholder="Notes for LineUp admin if changes are needed…">{{ old('comment') }}</textarea>
            </div>
            <div class="mfg-plan__review-actions">
                <button type="submit" name="decision" value="approved" class="mfg-plan__btn mfg-plan__btn--approve">
                    <i class="zmdi zmdi-check"></i> Approve For Manufacture
                </button>
                <button type="submit" name="decision" value="rejected" class="mfg-plan__btn mfg-plan__btn--reject" data-requires-comment>
                    <i class="zmdi zmdi-close"></i> Reject
                </button>
            </div>
            <p class="mfg-plan__review-hint">LineUp will manufacture the treatment plan after you approve.</p>
        </form>
        @elseif($isHistorical)
        <p class="mfg-plan__history-note">Kept for reference until the revised plan above is approved.</p>
        @endif
    </footer>
</article>
