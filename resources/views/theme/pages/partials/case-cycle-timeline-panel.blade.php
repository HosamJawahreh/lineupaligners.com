@php
    $timeline = $cycleTimeline ?? ['events' => [], 'grouped' => []];
    $events = $timeline['events'] ?? [];
    $totalCount = count($events);
    $panelTitle = $panelTitle ?? 'History';
    $panelSubtitle = $panelSubtitle ?? '';
    $emptyTitle = $emptyTitle ?? 'No activity yet';
    $emptyMessage = $emptyMessage ?? 'Activity will appear here as requests are submitted and plans are reviewed.';
    $timelineIdPrefix = $timelineIdPrefix ?? 'cycle-timeline';
    $compact = $compact ?? true;
@endphp

<div class="case-cycle-timeline @if($compact) case-cycle-timeline--compact @endif">
    <header class="case-cycle-timeline__head">
        <h4 class="case-cycle-timeline__title">{{ $panelTitle }}</h4>
        @if(filled($panelSubtitle))
        <p class="case-cycle-timeline__subtitle">{{ $panelSubtitle }}</p>
        @endif
        @if($totalCount > 0)
        <span class="case-cycle-timeline__count">{{ $totalCount }} event{{ $totalCount === 1 ? '' : 's' }}</span>
        @endif
    </header>

    @if($totalCount === 0)
    <div class="case-modification__history-empty">
        <i class="zmdi zmdi-time-restore" aria-hidden="true"></i>
        <p><strong>{{ $emptyTitle }}</strong><br>{{ $emptyMessage }}</p>
    </div>
    @else
    <div class="case-timeline case-timeline--embedded">
        @include('theme.pages.partials.case-timeline-track', [
            'timeline' => $timeline,
            'timelineIdPrefix' => $timelineIdPrefix,
            'trackLabel' => $panelTitle,
        ])
    </div>
    @endif
</div>
