@php
    $statusClass = match ($plan->review_status) {
        'approved' => 'is-approved',
        'rejected' => 'is-rejected',
        default => 'is-pending',
    };
    $isHistorical = ! empty($isHistorical);
    $showDoctorActions = ($canReview ?? false) && $plan->isPending() && $plan->is_current && ! $isHistorical;
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

    @include('theme.pages.partials.case-manufacture-plan-doctor-actions', [
        'plan' => $plan,
        'patient' => $patient ?? null,
        'canReview' => $canReview ?? false,
        'isHistorical' => $isHistorical,
    ])

    <div class="mfg-plan__canvas-wrap @if($showDoctorActions) mfg-plan__canvas-wrap--with-actions @endif">
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
    @if($plan->is_current && ! $isHistorical && isset($patient) && ! $plan->stage_number)
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

        @if($isHistorical)
        <p class="mfg-plan__history-note">Kept for reference until the revised plan above is approved.</p>
        @endif
    </footer>
</article>
