@extends('layouts.app')

@section('title', 'Dashboard')
@section('body-class', 'lineup-dashboard-page')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/lineup-home-dashboard.css') }}">
@endpush

@section('content')
<section class="content lineup-dashboard">
    <div class="lineup-dashboard__wrap">
        <header class="lineup-dashboard__hero">
            <div class="lineup-dashboard__hero-text">
                <p class="lineup-dashboard__eyebrow">{{ $greeting }}, {{ $userName }}</p>
                <h1 class="lineup-dashboard__title">{{ $isAdmin ? 'Operations overview' : 'Your practice overview' }}</h1>
                <p class="lineup-dashboard__subtitle">
                    @if($isAdmin)
                        Monitor aligner cases, doctor reviews, and manufacturing across {{ $clinicName }}.
                    @else
                        Track your cases, plan reviews, and manufacturing status for {{ $clinicName }}.
                    @endif
                </p>
            </div>
            <div class="lineup-dashboard__hero-actions">
                @foreach($quickActions as $action)
                <a href="{{ $action['href'] }}" class="lineup-dashboard__action-btn{{ !empty($action['primary']) ? ' is-primary' : '' }}">
                    <i class="zmdi {{ $action['icon'] }}"></i>
                    <span>{{ $action['label'] }}</span>
                </a>
                @endforeach
            </div>
        </header>

        <div class="lineup-dashboard__stats">
            @foreach($stats as $stat)
            <a href="{{ $stat['href'] }}" class="lineup-dashboard__stat lineup-dashboard__stat--{{ $stat['tone'] }}">
                <span class="lineup-dashboard__stat-icon"><i class="zmdi {{ $stat['icon'] }}"></i></span>
                <span class="lineup-dashboard__stat-body">
                    <span class="lineup-dashboard__stat-value">{{ number_format($stat['value']) }}</span>
                    <span class="lineup-dashboard__stat-label">{{ $stat['label'] }}</span>
                    <span class="lineup-dashboard__stat-hint">{{ $stat['hint'] }}</span>
                </span>
            </a>
            @endforeach
        </div>

        <div class="lineup-dashboard__grid">
            <div class="lineup-dashboard__main">
                <div class="lineup-dashboard__card">
                    <div class="lineup-dashboard__card-head">
                        <h2>Cases opened — last 7 days</h2>
                    </div>
                    <div class="lineup-dashboard__chart" role="img" aria-label="Cases created in the last seven days">
                        @foreach($casesChart as $bar)
                        <div class="lineup-dashboard__chart-col">
                            <span class="lineup-dashboard__chart-bar" style="height: {{ max($bar['height'], 4) }}%"></span>
                            <span class="lineup-dashboard__chart-count">{{ $bar['count'] }}</span>
                            <span class="lineup-dashboard__chart-label">{{ $bar['label'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="lineup-dashboard__card">
                    <div class="lineup-dashboard__card-head">
                        <h2>Recent cases</h2>
                        <a href="{{ route('patients.index') }}" class="lineup-dashboard__card-link">View all</a>
                    </div>
                    @if($recentCases->isEmpty())
                    <p class="lineup-dashboard__empty">No cases yet. Create your first aligner case to get started.</p>
                    @else
                    <div class="lineup-dashboard__table-wrap">
                        <table class="lineup-dashboard__table">
                            <thead>
                                <tr>
                                    <th>Case</th>
                                    <th>Patient</th>
                                    @if($isAdmin)<th>Doctor</th>@endif
                                    <th>Workflow</th>
                                    <th>Opened</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentCases as $case)
                                <tr>
                                    <td>
                                        <a href="{{ route('patients.show', $case) }}" class="lineup-dashboard__case-link">{{ $case->patient_id }}</a>
                                    </td>
                                    <td>{{ $case->fullName() }}</td>
                                    @if($isAdmin)
                                    <td>{{ $case->doctor?->fullName() ?? '—' }}</td>
                                    @endif
                                    <td>
                                        <span class="lineup-dashboard__badge lineup-dashboard__badge--{{ $case->workflowBadgeClass() }}">
                                            {{ $case->workflowStageLabel() }}
                                        </span>
                                    </td>
                                    <td>{{ $case->created_at?->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>

            <aside class="lineup-dashboard__aside">
                @if(count($workflowBreakdown) > 0)
                <div class="lineup-dashboard__card">
                    <div class="lineup-dashboard__card-head">
                        <h2>Workflow snapshot</h2>
                    </div>
                    <ul class="lineup-dashboard__workflow-list">
                        @foreach($workflowBreakdown as $row)
                        <li class="lineup-dashboard__workflow-item">
                            <span class="lineup-dashboard__workflow-label">{{ $row['label'] }}</span>
                            <span class="lineup-dashboard__workflow-count">{{ $row['count'] }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="lineup-dashboard__card">
                    <div class="lineup-dashboard__card-head">
                        <h2>Notifications</h2>
                        <a href="{{ route('notifications.index') }}" class="lineup-dashboard__card-link">View all</a>
                    </div>
                    @if($notifications->isEmpty())
                    <p class="lineup-dashboard__empty">No notifications yet.</p>
                    @else
                    <ul class="lineup-dashboard__notify-list">
                        @foreach($notifications as $notification)
                        @php $data = $notification->data; @endphp
                        <li class="lineup-dashboard__notify-item{{ $notification->read_at ? '' : ' is-unread' }}">
                            <a href="{{ $data['url'] ?? route('notifications.index') }}" class="lineup-dashboard__notify-link">
                                <span class="lineup-dashboard__notify-icon">
                                    <i class="zmdi {{ $data['icon'] ?? 'zmdi-notifications' }}"></i>
                                </span>
                                <span class="lineup-dashboard__notify-body">
                                    <span class="lineup-dashboard__notify-title">{{ $data['title'] ?? 'Notification' }}</span>
                                    <span class="lineup-dashboard__notify-text">{{ Str::limit($data['body'] ?? '', 72) }}</span>
                                    <span class="lineup-dashboard__notify-time">{{ $notification->created_at->diffForHumans() }}</span>
                                </span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
