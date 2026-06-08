@php
    $user = auth()->user();
    $userRole = $user->isAdmin() ? 'Administrator' : 'Doctor';
    $profileUrl = route('profile.edit');
    $settingsUrl = $user->isAdmin() ? route('settings.index') : route('doctor.clinic-settings.edit');
@endphp
<nav class="navbar lineup-topbar" role="banner" aria-label="Application header">
    <div class="lineup-topbar-start">
        <a href="javascript:void(0);" class="bars lineup-topbar-btn lineup-topbar-btn-menu d-lg-none" aria-label="Open menu"></a>
        <a href="{{ route('dashboard') }}" class="lineup-topbar-brand" title="{{ $projectName ?? config('app.name') }}">
            <span class="lineup-topbar-logo-wrap">
                <img src="{{ $logoUrl ?? asset('assets/images/logo.svg') }}" alt="{{ $projectName ?? config('app.name') }} logo">
            </span>
            <span class="lineup-topbar-brand-text">
                <span class="lineup-topbar-brand-name">{{ $projectName ?? config('app.name') }}</span>
                <span class="lineup-topbar-brand-tagline">Aligner Management</span>
            </span>
        </a>
    </div>

    <div class="lineup-topbar-actions">
        <div class="lineup-topbar-tools">
            @include('layouts.partials.lineup-notifications')
            <button type="button"
                    id="lineup-theme-toggle"
                    class="lineup-topbar-btn lineup-topbar-btn-theme"
                    title="Switch to dark mode"
                    aria-label="Switch to dark mode"
                    aria-pressed="false">
                <span class="lineup-theme-icon" aria-hidden="true">
                    <i class="zmdi zmdi-sun lineup-theme-icon--sun"></i>
                    <i class="zmdi zmdi-brightness-2 lineup-theme-icon--moon"></i>
                </span>
            </button>
            <a href="{{ route('website.home') }}"
               class="lineup-topbar-btn"
               title="Visit website"
               aria-label="Visit website"
               target="_blank"
               rel="noopener noreferrer">
                <i class="zmdi zmdi-globe"></i>
            </a>
            <a href="{{ $settingsUrl }}" class="lineup-topbar-btn d-none d-md-inline-flex" title="Settings" aria-label="Settings">
                <i class="zmdi zmdi-settings"></i>
            </a>
        </div>

        <span class="lineup-topbar-divider d-none d-md-inline-block" aria-hidden="true"></span>

        <a href="{{ $profileUrl }}" class="lineup-topbar-user" title="My profile">
            <img src="{{ $user->photoUrl() }}" alt="" width="32" height="32">
            <span class="lineup-topbar-user-meta d-none d-sm-flex">
                <span class="lineup-topbar-user-name">{{ $user->displayName() }}</span>
                <span class="lineup-topbar-user-role">{{ $userRole }}</span>
            </span>
        </a>

        <a href="{{ route('logout') }}" class="lineup-topbar-btn lineup-topbar-btn-signout" title="Sign out" aria-label="Sign out"
           onclick="event.preventDefault();document.getElementById('logout-form').submit();">
            <i class="zmdi zmdi-power"></i>
        </a>
    </div>
</nav>
