@extends('layouts.app')

@section('title', 'Notifications')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/lineup-notifications.css') }}">
@endpush

@section('content')
<section class="content lineup-notify-page">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-6 col-sm-12">
                <h2>Notifications<small>Case activity and messages</small></h2>
            </div>
            <ul class="breadcrumb float-md-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i></a></li>
                <li class="breadcrumb-item active">Notifications</li>
            </ul>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card lineup-notify-card">
            <div class="header d-flex justify-content-between align-items-center flex-wrap">
                <h2 class="m-b-0"><strong>All</strong> notifications</h2>
                @if(auth()->user()->unreadNotifications()->count() > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}" class="m-b-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-default btn-round">Mark all as read</button>
                </form>
                @endif
            </div>
            <div class="body p-0">
                <ul class="lineup-notify-page__list">
                    @forelse($notifications as $notification)
                        @php
                            $data = $notification->data;
                            $isUnread = $notification->read_at === null;
                        @endphp
                        <li @class(['lineup-notify-page__item', 'is-unread' => $isUnread])>
                            <a href="{{ $data['url'] ?? route('dashboard') }}" class="lineup-notify-page__link" data-notification-id="{{ $notification->id }}">
                                <span class="lineup-notify-page__icon">
                                    <i class="zmdi {{ $data['icon'] ?? 'zmdi-notifications' }}"></i>
                                </span>
                                <span class="lineup-notify-page__body">
                                    <span class="lineup-notify-page__title">{{ $data['title'] ?? 'Notification' }}</span>
                                    <span class="lineup-notify-page__text">{{ $data['body'] ?? '' }}</span>
                                    <span class="lineup-notify-page__time">{{ $notification->created_at->diffForHumans() }}</span>
                                </span>
                                @if($isUnread)
                                <span class="lineup-notify-page__dot" aria-hidden="true"></span>
                                @endif
                            </a>
                        </li>
                    @empty
                        <li class="lineup-notify-page__empty">You have no notifications yet.</li>
                    @endforelse
                </ul>
            </div>
            @if($notifications->hasPages())
            <div class="footer text-center">
                {{ $notifications->links() }}
            </div>
            @endif
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function () {
    $('.lineup-notify-page__link[data-notification-id]').on('click', function () {
        var id = $(this).data('notification-id');
        if (window.lineupNotifyMarkRead) {
            window.lineupNotifyMarkRead(id);
        }
    });
});
</script>
@endpush
