@php
    $bool = fn (string $key, bool $default = true) => filter_var(old($key, $settings[$key] ?? ($default ? '1' : '0')), FILTER_VALIDATE_BOOLEAN);
    $types = $notificationTypes ?? [];
    $typeLabels = config('lineup-notifications.type_labels', []);
    $typeRecipients = config('lineup-notifications.type_recipients', []);
@endphp

<div class="tab-pane" id="tab-notifications" role="tabpanel">
    <p class="settings-section-title">Notifications</p>
    <p class="text-muted m-b-25">Control in-app alerts, email delivery, and notification sounds for each case workflow event.</p>

    <div class="settings-panel-grid">
        <div class="inner-card">
            <h6>Global toggles</h6>
            <ul class="setting-list list-unstyled m-b-0">
                <li>
                    <div class="checkbox">
                        <input id="page-notifications-enabled" name="notifications_enabled" type="checkbox" value="1" form="settings-form" @checked($bool('notifications_enabled'))>
                        <label for="page-notifications-enabled">Enable notifications</label>
                    </div>
                    <small class="text-muted d-block m-l-30">Master switch for all in-app and email alerts.</small>
                </li>
                <li class="m-t-15">
                    <div class="checkbox">
                        <input id="page-notification-email" name="notification_email_enabled" type="checkbox" value="1" form="settings-form" @checked($bool('notification_email_enabled'))>
                        <label for="page-notification-email">Send email notifications</label>
                    </div>
                    <small class="text-muted d-block m-l-30">Uses SMTP from <code>.env</code> (MAIL_*). Queue: {{ config('lineup-notifications.email.queue', true) ? 'enabled' : 'disabled' }}.</small>
                </li>
                <li class="m-t-15">
                    <div class="checkbox">
                        <input id="page-notification-sound" name="notification_sound_enabled" type="checkbox" value="1" form="settings-form" @checked($bool('notification_sound_enabled'))>
                        <label for="page-notification-sound">Play notification sound</label>
                    </div>
                    <small class="text-muted d-block m-l-30">Chime when new in-app alerts arrive.</small>
                </li>
            </ul>
        </div>

        <div class="inner-card settings-notification-matrix">
            <h6>Per-event channels</h6>
            <p class="text-muted small m-b-15">Choose which events send in-app alerts and email for each workflow step.</p>
            <div class="table-responsive">
                <table class="table table-sm m-b-0">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Recipients</th>
                            <th class="text-center">In-app</th>
                            <th class="text-center">Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(config('lineup-notifications.types', []) as $type)
                            @php
                                $row = $types[$type] ?? ['in_app' => true, 'email' => true];
                                $inApp = filter_var(old("notification_types.{$type}.in_app", $row['in_app']), FILTER_VALIDATE_BOOLEAN);
                                $email = filter_var(old("notification_types.{$type}.email", $row['email']), FILTER_VALIDATE_BOOLEAN);
                            @endphp
                            <tr>
                                <td>{{ $typeLabels[$type] ?? $type }}</td>
                                <td><small class="text-muted">{{ $typeRecipients[$type] ?? '—' }}</small></td>
                                <td class="text-center">
                                    <input type="hidden" name="notification_types[{{ $type }}][in_app]" value="0" form="settings-form">
                                    <input type="checkbox" name="notification_types[{{ $type }}][in_app]" value="1" form="settings-form" @checked($inApp)>
                                </td>
                                <td class="text-center">
                                    <input type="hidden" name="notification_types[{{ $type }}][email]" value="0" form="settings-form">
                                    <input type="checkbox" name="notification_types[{{ $type }}][email]" value="1" form="settings-form" @checked($email)>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
