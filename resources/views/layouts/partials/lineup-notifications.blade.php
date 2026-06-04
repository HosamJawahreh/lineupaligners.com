@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp
<div class="lineup-notify" id="lineup-notify"
     data-feed-url="{{ route('notifications.feed') }}"
     data-read-base="{{ url('notifications') }}"
     data-read-all-url="{{ route('notifications.read-all') }}"
     data-sound-url="{{ asset(config('lineup-notifications.sound', 'assets/sounds/notification.mp3')) }}?v=5"
     data-csrf="{{ csrf_token() }}">
    <button type="button" class="lineup-topbar-btn lineup-notify__trigger d-inline-flex" id="lineup-notify-trigger" aria-expanded="false" aria-haspopup="true" title="Notifications" aria-label="Notifications">
        <i class="zmdi zmdi-notifications"></i>
        <span class="lineup-notify__badge" id="lineup-notify-badge" @if($unreadCount < 1) hidden @endif>{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
    </button>

    <div class="lineup-notify__dropdown" id="lineup-notify-dropdown" hidden>
        <div class="lineup-notify__dropdown-head">
            <strong>Notifications</strong>
            <a href="{{ route('notifications.index') }}" class="lineup-notify__view-all">View all</a>
        </div>
        <div class="lineup-notify__list" id="lineup-notify-list">
            <p class="lineup-notify__empty" id="lineup-notify-empty">No notifications yet.</p>
        </div>
        <div class="lineup-notify__dropdown-foot">
            <button type="button" class="lineup-notify__mark-all" id="lineup-notify-mark-all">Mark all as read</button>
        </div>
    </div>
</div>
<audio id="lineup-notify-audio" class="lineup-notify-audio" preload="auto" playsinline
       src="{{ asset(config('lineup-notifications.sound', 'assets/sounds/notification.mp3')) }}?v=5"></audio>
