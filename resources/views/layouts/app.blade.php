<!doctype html>
<html class="no-js " lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
@include('layouts.partials.document-head-meta')
@include('layouts.partials.favicon')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-select/css/bootstrap-select.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/jvectormap/jquery-jvectormap-2.0.3.min.css') }}"/>
<link rel="stylesheet" href="{{ asset('assets/plugins/morrisjs/morris.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/color_skins.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/theme-skins-extra.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/settings-panel.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/sweetalert-toast.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/forms-fix.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/jquery-datatable/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-topbar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/cases-dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-buttons.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-datatables.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-loader.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-typography.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-notifications.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-call-support.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/scan-upload-overlay.css') }}">
@stack('styles')
</head>
<body class="lineup-app {{ $bodyThemeClass ?? 'theme-cyan' }} {{ $bodyMenuClasses ?? '' }} @yield('body-class')" style="--lineup-skin: {{ $themeSkinColor ?? '#00cfd1' }};">
<div class="page-loader-wrapper lineup-page-loader">
    <div class="loader">
        <div class="lineup-ios-spinner" role="status" aria-live="polite" aria-label="Loading">
            @for ($i = 0; $i < 12; $i++)
            <span></span>
            @endfor
        </div>
    </div>
</div>
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
<script src="{{ asset('assets/js/alerts.js') }}"></script>
@include('layouts.partials.flash-sweetalert')
<script>
    window.LINEUP_USER_ID = @json(auth()->id());
</script>
<script src="{{ asset('assets/js/lineup-notifications.js') }}"></script>
<script src="{{ asset('assets/js/scan-upload-loading.js') }}"></script>
@stack('scripts')
</body>
</html>