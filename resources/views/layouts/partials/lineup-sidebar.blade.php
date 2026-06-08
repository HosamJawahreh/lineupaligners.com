<aside id="leftsidebar" class="sidebar lineup-sidebar">
    <div class="lineup-sidebar-mobile-head">
        <img src="{{ $logoUrl ?? asset('assets/images/logo.svg') }}" alt="">
        <div class="lineup-sidebar-mobile-head__text">
            <strong>{{ $projectName ?? config('app.name') }}</strong>
            <small>Dashboard</small>
        </div>
        <button type="button" class="lineup-sidebar-close" aria-label="Close menu">
            <i class="zmdi zmdi-close"></i>
        </button>
    </div>
    <div class="tab-content">
        <div class="tab-pane active" id="lineup-nav-pane">
            <div class="menu">
                @include('layouts.partials.lineup-sidebar-nav')
            </div>
        </div>
    </div>
</aside>
