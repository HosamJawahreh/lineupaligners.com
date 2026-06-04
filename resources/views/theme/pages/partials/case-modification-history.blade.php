@php
    $timeline = $caseTimeline ?? ['events' => [], 'grouped' => []];
    $events = $timeline['events'] ?? [];
    $grouped = $timeline['grouped'] ?? [];
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
            <i class="zmdi zmdi-time-restore-setting"></i>
        </div>
        <h4>No activity yet</h4>
        <p>Case events will appear here as plans are uploaded, reviewed, and modifications are requested.</p>
    </div>
    @else
    <div class="case-timeline__track" role="list" aria-label="Case activity timeline">
        @foreach($grouped as $dateKey => $dayEvents)
        @php
            $dayLabel = $dayEvents[0]['date_label'] ?? $dateKey;
            $isToday = ($dayEvents[0]['occurred_at'] ?? null)?->isToday() ?? false;
        @endphp
        <section class="case-timeline__day" role="group" aria-label="Events on {{ $dayLabel }}">
            <div class="case-timeline__day-marker">
                <time class="case-timeline__day-date" datetime="{{ $dateKey }}">
                    {{ $dayLabel }}
                    @if($isToday)
                    <span class="case-timeline__day-today">Today</span>
                    @endif
                </time>
            </div>

            <div class="case-timeline__day-events">
                @foreach($dayEvents as $event)
                <article class="case-timeline__event case-timeline__event--{{ $event['tone'] }} @if($event['is_latest']) is-latest @endif @if($event['is_active']) is-active @endif"
                         role="listitem"
                         id="timeline-{{ $event['id'] }}">
                    <div class="case-timeline__rail" aria-hidden="true">
                        <span class="case-timeline__node">
                            <i class="zmdi {{ $event['icon'] }}" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div class="case-timeline__card">
                        <div class="case-timeline__card-top">
                            <div class="case-timeline__card-head">
                                <h4 class="case-timeline__event-title">{{ $event['title'] }}</h4>
                                <time class="case-timeline__event-time" datetime="{{ $event['occurred_at']?->toIso8601String() }}">
                                    {{ $event['time_label'] }}
                                </time>
                            </div>
                            @if(! empty($event['badges']))
                            <ul class="case-timeline__badges" aria-label="Status tags">
                                @foreach($event['badges'] as $badge)
                                <li class="case-timeline__badge case-timeline__badge--{{ $badge['variant'] }}">{{ $badge['label'] }}</li>
                                @endforeach
                            </ul>
                            @endif
                        </div>

                        @if(! empty($event['summary']))
                        <p class="case-timeline__summary">{{ $event['summary'] }}</p>
                        @endif

                        @if(! empty($event['body']))
                        <div class="case-timeline__body case-timeline__body--{{ $event['type'] }}">
                            @if($event['type'] === 'plan_rejected')
                            <span class="case-timeline__body-label">Doctor feedback</span>
                            @elseif($event['type'] === 'modification_requested')
                            <span class="case-timeline__body-label">Modification notes</span>
                            @elseif($event['type'] === 'refinement_ordered')
                            <span class="case-timeline__body-label">Refinement notes</span>
                            @elseif($event['type'] === 'plan_uploaded')
                            <span class="case-timeline__body-label">Canvas link</span>
                            @endif
                            <p>{{ $event['body'] }}</p>
                        </div>
                        @endif

                        @if(! empty($event['actor_name']))
                        <footer class="case-timeline__actor">
                            <span class="case-timeline__actor-avatar" aria-hidden="true">
                                <i class="zmdi zmdi-account"></i>
                            </span>
                            <span class="case-timeline__actor-text">
                                <strong>{{ $event['actor_name'] }}</strong>
                                @if(! empty($event['actor_role']))
                                <span class="case-timeline__actor-role">{{ $event['actor_role'] }}</span>
                                @endif
                            </span>
                        </footer>
                        @endif

                        @if($event['is_latest'])
                        <span class="case-timeline__latest-tag">Latest activity</span>
                        @endif
                    </div>
                </article>
                @endforeach
            </div>
        </section>
        @endforeach
    </div>
    @endif
</div>
