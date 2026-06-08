@php
    $timeline = $caseTimeline ?? ['events' => [], 'grouped' => []];
    $events = $timeline['events'] ?? [];
    $totalCount = count($events);
@endphp

<div class="case-timeline" id="case-modification-history">
    <header class="case-timeline__header">
        <div class="case-timeline__header-text">
            <h3 class="case-timeline__title">Case history</h3>
            <p class="case-timeline__subtitle">
                A complete audit trail for this case — plans, reviews, manufacturing, modifications, and milestones in chronological order.
            </p>
        </div>
        @if($totalCount > 0)
        <div class="case-timeline__stat" aria-label="{{ $totalCount }} events recorded">
            <span class="case-timeline__stat-value">{{ $totalCount }}</span>
            <span class="case-timeline__stat-label">Events</span>
        </div>
        @endif
    </header>

    @if($totalCount === 0)
    <div class="case-timeline__empty">
        <div class="case-timeline__empty-icon" aria-hidden="true">
            <i class="zmdi zmdi-time-restore"></i>
        </div>
        <h4>No activity yet</h4>
        <p>Case events will appear here as plans are uploaded, reviewed, and modifications are requested.</p>
    </div>
    @else
    @include('theme.pages.partials.case-timeline-track', [
        'timeline' => $timeline,
        'timelineIdPrefix' => 'timeline',
        'trackLabel' => 'Case activity timeline',
    ])
    @endif
</div>
