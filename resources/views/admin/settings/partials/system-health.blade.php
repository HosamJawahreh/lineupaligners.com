@php
    $stats = $systemStats ?? [];
    $casesByStage = $stats['cases_by_stage'] ?? [];
    $stageLabels = collect(config('patient-case-workflow.badge_classes', []))->keys()->mapWithKeys(fn ($k) => [$k => ucfirst(str_replace('_', ' ', $k))]);
    $selectedTz = old('system_timezone', $settings['system_timezone'] ?? 'UTC');
@endphp

<div class="tab-pane" id="tab-system" role="tabpanel">
    <p class="settings-section-title">System</p>
    <p class="text-muted m-b-25">Timezone, upload limits, queue status, and live case metrics.</p>

    <div class="settings-panel-grid">
        <div class="inner-card">
            <h6>Timezone</h6>
            <p class="text-muted small m-b-15">Dates, timestamps, and dashboards use this timezone for the entire system.</p>
            <div class="form-group m-b-10">
                <label for="page-system-timezone">System timezone</label>
                <select name="system_timezone" id="page-system-timezone" class="form-control" form="settings-form" required>
                    @foreach(config('settings.timezones', ['UTC' => 'UTC']) as $tz => $label)
                        <option value="{{ $tz }}" @selected($selectedTz === $tz)>{{ $label }} ({{ $tz }})</option>
                    @endforeach
                </select>
            </div>
            <div class="summary-item"><span>Current offset</span><span>{{ $stats['timezone_offset'] ?? now()->format('P') }}</span></div>
            <div class="summary-item"><span>Server time</span><span>{{ now()->format('M j, Y g:i A') }}</span></div>
        </div>

        <div class="inner-card">
            <h6>Cases by stage</h6>
            @if(count($casesByStage) === 0)
                <p class="text-muted small m-b-0">No workflow data yet.</p>
            @else
                @foreach($casesByStage as $stage => $count)
                    <div class="summary-item">
                        <span>{{ $stageLabels[$stage] ?? ucfirst(str_replace('_', ' ', (string) $stage)) }}</span>
                        <span>{{ number_format($count) }}</span>
                    </div>
                @endforeach
                <div class="summary-item m-t-10"><span><strong>Total cases</strong></span><span><strong>{{ number_format($stats['total_cases'] ?? 0) }}</strong></span></div>
                <div class="summary-item"><span>Created today</span><span>{{ number_format($stats['cases_today'] ?? 0) }}</span></div>
            @endif
        </div>

        <div class="inner-card">
            <h6>Queue &amp; mail</h6>
            <div class="summary-item"><span>Queue driver</span><span>{{ $stats['queue_connection'] ?? '—' }}</span></div>
            <div class="summary-item"><span>Pending jobs</span><span>{{ number_format($stats['pending_jobs'] ?? 0) }}</span></div>
            <div class="summary-item"><span>Failed jobs</span><span @class(['text-danger' => ($stats['failed_jobs'] ?? 0) > 0])>{{ number_format($stats['failed_jobs'] ?? 0) }}</span></div>
            <div class="summary-item"><span>Mail driver</span><span>{{ $stats['mail_mailer'] ?? '—' }}</span></div>
            <div class="summary-item"><span>Mail queue</span><span>{{ ($stats['mail_queue'] ?? false) ? 'On' : 'Off' }}</span></div>
            @if(($stats['failed_jobs'] ?? 0) > 0)
                <p class="text-danger small m-t-15 m-b-0">Failed jobs detected — run <code>php artisan queue:failed</code> to inspect.</p>
            @elseif(($stats['mail_queue'] ?? false) && ($stats['pending_jobs'] ?? 0) > 0)
                <p class="text-muted small m-t-15 m-b-0">Ensure <code>php artisan queue:work</code> is running for email delivery.</p>
            @endif
        </div>

        <div class="inner-card">
            <h6>Server resources</h6>
            <div class="summary-item"><span>Memory (PHP)</span><span>{{ $stats['memory_mb'] ?? 0 }} MB</span></div>
            <div class="summary-item"><span>CPU load</span><span>{{ $stats['cpu_percent'] ?? 0 }}%</span></div>
            <div class="summary-item"><span>Disk used</span><span>{{ $stats['disk_percent'] ?? 0 }}%</span></div>
            <div class="summary-item"><span>Disk free</span><span>{{ $stats['disk_free_gb'] ?? 0 }} GB</span></div>
            <div class="summary-item"><span>Upload limit</span><span>{{ $stats['php_upload_max'] ?? '—' }}</span></div>
            <div class="summary-item"><span>POST limit</span><span>{{ $stats['php_post_max'] ?? '—' }}</span></div>
            <div class="summary-item"><span>Environment</span><span>{{ config('app.env') }}</span></div>
        </div>
    </div>
</div>
