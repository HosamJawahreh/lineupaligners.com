@extends('layouts.app')

@section('title', 'Contact Requests')

@section('content')
<section class="content cases-dashboard">
    <div class="container-fluid">
        <div class="cases-panel">
            <div class="cases-panel-head">
                <div>
                    <h1>Contact Requests</h1>
                    <p class="text-muted m-b-0">Messages submitted through the public website contact form.</p>
                </div>
                <div class="cases-head-actions">
                    @if($unreadCount > 0)
                    <form method="POST" action="{{ route('admin.contact-requests.read-all') }}" class="m-b-0">
                        @csrf
                        <button type="submit" class="cases-btn cases-btn-outline">
                            <i class="zmdi zmdi-check-all"></i>
                            <span>Mark all read</span>
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <div class="cases-table-scroll">
                <div class="cases-table-wrap">
                    <table class="cases-table table table-hover w-100">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Message</th>
                                <th>Submitted</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inquiries as $inquiry)
                            <tr @class(['cases-row-unread' => ! $inquiry->isRead()])>
                                <td>
                                    @if($inquiry->isRead())
                                    <span class="cases-badge cases-badge-active">Read</span>
                                    @else
                                    <span class="cases-badge cases-badge-pending">New</span>
                                    @endif
                                </td>
                                <td><strong>{{ $inquiry->name }}</strong></td>
                                <td><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></td>
                                <td>{{ $inquiry->phone ?: '—' }}</td>
                                <td>{{ Str::limit($inquiry->message, 80) }}</td>
                                <td><span class="text-nowrap">{{ $inquiry->created_at?->format('Y/m/d H:i') }}</span></td>
                                <td>
                                    <div class="cases-actions justify-content-end">
                                        <a href="{{ route('admin.contact-requests.show', $inquiry) }}" class="cases-action-btn" title="View request">
                                            <i class="zmdi zmdi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted p-30">No contact requests yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($inquiries->hasPages())
            <div class="cases-footer">
                {{ $inquiries->links('pagination::bootstrap-4', ['class' => 'cases-pagination']) }}
            </div>
            @endif
        </div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cases-dashboard.css') }}">
<style>
.cases-row-unread td {
    background: rgba(13, 148, 136, 0.06);
}
</style>
@endpush
