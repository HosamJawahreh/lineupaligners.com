@php
    $resolvedDashboardFont = \App\Models\Setting::dashboardFont();
    $dashboardFontUrl = $dashboardFontUrl ?? $resolvedDashboardFont['google_url'];
    $dashboardFontStack = $dashboardFontStack ?? $resolvedDashboardFont['stack'];
    $dashboardColorMode = $dashboardColorMode ?? \App\Models\Setting::dashboardColorMode();
    $bodyColorClass = $bodyColorClass ?? ('lineup-color-'.$dashboardColorMode);
@endphp
<!doctype html>
<html class="no-js " lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta content="width=device-width, initial-scale=1, viewport-fit=cover" name="viewport">
@include('layouts.partials.document-head-meta')
@include('layouts.partials.favicon')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
@if(! empty($dashboardFontUrl))
<link href="{{ $dashboardFontUrl }}" rel="stylesheet">
@endif
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-select/css/bootstrap-select.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/jvectormap/jquery-jvectormap-2.0.3.min.css') }}"/>
<link rel="stylesheet" href="{{ asset('assets/plugins/morrisjs/morris.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/color_skins.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/theme-skins-extra.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/settings-panel.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/sweetalert-toast.css') }}?v=5">
<link rel="stylesheet" href="{{ asset('assets/css/forms-fix.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/jquery-datatable/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-dashboard.css') }}?v=3">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-topbar.css') }}?v=4">
<link rel="stylesheet" href="{{ asset('assets/css/cases-dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-buttons.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-datatables.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-loader.css') }}?v=3">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-typography.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-theme-mode.css') }}?v=2">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-notifications.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-call-support.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/scan-upload-overlay.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-brand-system.css') }}?v=2">
@include('layouts.partials.brand-css-vars')
@stack('styles')
<link rel="stylesheet" href="{{ asset('assets/css/lineup-form-pages.css') }}?v=3">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-responsive.css') }}?v=2">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-mobile.css') }}?v=5">
</head>
<body class="lineup-app {{ $bodyThemeClass ?? 'theme-cyan' }} {{ $bodyColorClass }} {{ $bodyMenuClasses ?? '' }} @yield('body-class')"
      data-default-color-mode="{{ $dashboardColorMode }}"
      style="{{ $brandInlineStyle ?? '' }}">
<script>
(function () {
    try {
        var stored = localStorage.getItem('lineup-color-mode');
        if (stored === 'dark' || stored === 'light') {
            document.body.classList.remove('lineup-color-light', 'lineup-color-dark');
            document.body.classList.add('lineup-color-' + stored);
            document.documentElement.style.colorScheme = stored;
        }
    } catch (e) {}
})();
</script>
@include('layouts.partials.lineup-page-loader', ['loaderAriaLabel' => 'Loading'])
<div class="overlay"></div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

@include('layouts.partials.theme-shell')

@yield('content')

<script src="{{ asset('assets/bundles/libscripts.bundle.js') }}"></script>
<script src="{{ asset('assets/bundles/vendorscripts.bundle.js') }}"></script>
@stack('scripts-before-main')
<script src="{{ asset('assets/bundles/mainscripts.bundle.js') }}"></script>
<script src="{{ asset('assets/js/forms-fix.js') }}"></script>
<script src="{{ asset('assets/bundles/datatablescripts.bundle.js') }}"></script>
<script src="{{ asset('assets/js/lineup-datatables.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25/dist/sweetalert2.all.min.js"></script>
<script src="{{ asset('assets/js/alerts.js') }}?v=5"></script>
@include('layouts.partials.flash-sweetalert')
<script>
    window.LINEUP_USER_ID = @json(auth()->id());
</script>
<script src="{{ asset('assets/js/lineup-theme-mode.js') }}"></script>
<script src="{{ asset('assets/js/lineup-mobile-nav.js') }}?v=2"></script>
<script src="{{ asset('assets/js/lineup-notifications.js') }}"></script>
<script src="{{ asset('assets/js/scan-upload-loading.js') }}"></script>
@stack('scripts')
</body>
</html>