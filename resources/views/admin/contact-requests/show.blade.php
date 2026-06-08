@extends('layouts.app')

@section('title', 'Contact Request')

@section('content')
<section class="content cases-dashboard">
    <div class="container-fluid">
        <div class="cases-panel">
            <div class="cases-panel-head">
                <div>
                    <h1>Contact Request</h1>
                    <p class="text-muted m-b-0">Submitted {{ $inquiry->created_at?->format('M j, Y g:i A') }}</p>
                </div>
                <div class="cases-head-actions">
                    <a href="{{ route('admin.contact-requests.index') }}" class="cases-btn cases-btn-outline">
                        <i class="zmdi zmdi-arrow-left"></i>
                        <span>Back to list</span>
                    </a>
                    <a href="mailto:{{ $inquiry->email }}" class="cases-btn cases-btn-primary">
                        <i class="zmdi zmdi-email"></i>
                        <span>Reply by email</span>
                    </a>
                </div>
            </div>

            <div class="card m-b-0">
                <div class="body">
                    <div class="row">
                        <div class="col-md-6 m-b-20">
                            <h5 class="m-b-5">Name</h5>
                            <p class="m-b-0">{{ $inquiry->name }}</p>
                        </div>
                        <div class="col-md-6 m-b-20">
                            <h5 class="m-b-5">Status</h5>
                            <p class="m-b-0">
                                @if($inquiry->isRead())
                                <span class="cases-badge cases-badge-active">Read</span>
                                @if($inquiry->read_at)
                                <span class="text-muted"> · {{ $inquiry->read_at->format('M j, Y g:i A') }}</span>
                                @endif
                                @else
                                <span class="cases-badge cases-badge-pending">New</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 m-b-20">
                            <h5 class="m-b-5">Email</h5>
                            <p class="m-b-0"><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></p>
                        </div>
                        <div class="col-md-6 m-b-20">
                            <h5 class="m-b-5">Phone</h5>
                            <p class="m-b-0">{{ $inquiry->phone ?: '—' }}</p>
                        </div>
                        <div class="col-md-12 m-b-20">
                            <h5 class="m-b-5">Message</h5>
                            <div class="p-15" style="background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;white-space:pre-wrap;">{{ $inquiry->message }}</div>
                        </div>
                        <div class="col-md-6 m-b-0">
                            <h5 class="m-b-5">Form</h5>
                            <p class="m-b-0">{{ $inquiry->formTypeLabel() }}</p>
                        </div>
                        <div class="col-md-6 m-b-0">
                            <h5 class="m-b-5">Locale / IP</h5>
                            <p class="m-b-0">{{ strtoupper($inquiry->locale) }} · {{ $inquiry->ip_address ?: '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cases-dashboard.css') }}">
@endpush
