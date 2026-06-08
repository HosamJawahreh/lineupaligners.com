@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/settings-layout.css') }}?v=2">
@endpush

@section('content')
<section class="content settings-page">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-6 col-sm-12">
                <h2>@yield('settings-heading', 'System Settings')<small>@yield('settings-subheading', 'Manage your configuration')</small></h2>
            </div>
            <ul class="breadcrumb float-md-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i></a></li>
                <li class="breadcrumb-item active">@yield('settings-breadcrumb', 'Settings')</li>
            </ul>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card settings-card">
            <div class="header settings-tabs-header">
                <ul class="nav nav-tabs settings-tabs-nav" role="tablist">
                    @yield('settings-tabs')
                </ul>
            </div>
            <div class="body">
                <div class="tab-content">
                    @yield('settings-panels')
                </div>
                <div class="settings-actions settings-actions-main" id="settings-save-actions">
                    <button type="submit" form="settings-form" class="btn btn-primary btn-round">
                        <i class="zmdi zmdi-check m-r-5"></i> Save All Settings
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-default btn-round btn-simple">Cancel</a>
                    @yield('settings-actions')
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $formAction ?? route('settings.update') }}" id="settings-form" enctype="multipart/form-data" class="d-none">
        @csrf
        @method($formMethod ?? 'PUT')
    </form>
</section>
@endsection

@push('scripts')
@if(session('success'))
<script>
(function () {
    var adminDefault = @json(\App\Models\Setting::dashboardColorMode());
    try {
        localStorage.setItem('lineup-color-mode-admin-default', adminDefault);
        localStorage.removeItem('lineup-color-mode');
        document.body.classList.remove('lineup-color-light', 'lineup-color-dark');
        document.body.classList.add('lineup-color-' + adminDefault);
        document.documentElement.style.colorScheme = adminDefault;
    } catch (e) {}
})();
</script>
@endif
<script>
$(function () {
    var tab = new URLSearchParams(window.location.search).get('tab');
    if (tab) {
        $('.settings-page .nav-tabs a[href="#tab-' + tab + '"]').tab('show');
    }

    function toggleSaveActions() {
        var onRoles = $('.settings-page .nav-tabs a[href="#tab-doctor-roles"]').hasClass('active');
        $('#settings-save-actions').toggle(!onRoles);
    }

    $('.settings-page .nav-tabs a[data-toggle="tab"]').on('shown.bs.tab', toggleSaveActions);
    toggleSaveActions();

    $('.settings-skin-picker li').on('click', function () {
        var $li = $(this);
        $li.siblings().removeClass('active');
        $li.addClass('active');
        $li.find('input[type=radio]').prop('checked', true);
    });

    function applyFontPreview($input) {
        if (!$input.length) {
            return;
        }
        var stack = $input.data('font-stack') || '';
        $('#settings-font-preview-title, #settings-font-preview-body').css('font-family', stack);
    }

    $('.settings-font-picker li').on('click', function () {
        var $li = $(this);
        $li.siblings().removeClass('active');
        $li.addClass('active');
        var $input = $li.find('input[type=radio]').prop('checked', true);
        applyFontPreview($input);
    });

    applyFontPreview($('.settings-font-picker input[type=radio]:checked'));

    function applyColorModePreview(mode) {
        mode = mode === 'dark' ? 'dark' : 'light';
        document.body.classList.remove('lineup-color-light', 'lineup-color-dark');
        document.body.classList.add('lineup-color-' + mode);
        document.documentElement.style.colorScheme = mode;
    }

    $('.settings-color-mode-picker li').on('click', function () {
        var $li = $(this);
        $li.siblings().removeClass('active');
        $li.addClass('active');
        var $input = $li.find('input[type=radio]').prop('checked', true);
        applyColorModePreview($input.val());
    });

    $('.theme-light-dark label.btn').on('click', function () {
        $(this).closest('.theme-light-dark').find('label.btn').removeClass('active');
        $(this).addClass('active');
        $(this).find('input[type=radio]').prop('checked', true);
    });

    $('#logo-input').on('change', function () {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#logo-preview').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    });

    $('#profile-photo-input').on('change', function () {
        var file = this.files[0];
        if (!file) return;
        $('#remove_photo').prop('checked', false);
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#profile-photo-preview').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    });

    $('#remove_photo').on('change', function () {
        if (this.checked) {
            $('#profile-photo-input').val('');
            $('#profile-photo-preview').attr('src', @json(asset('assets/images/profile_av.jpg')));
        }
    });

    function normalizeHexInput(value, fallback) {
        var hex = (value || '').trim();
        if (!hex) return fallback;
        if (hex.charAt(0) !== '#') hex = '#' + hex;
        return /^#[0-9a-fA-F]{6}$/.test(hex) ? hex.toLowerCase() : fallback;
    }

    function syncBrandPreview() {
        var skinFallback = $('.settings-skin-picker li.active .skin-swatch').css('background-color') || '#1a7fd4';
        var primary = normalizeHexInput($('#brand_primary').val(), normalizeHexInput($('#brand_primary_picker').val(), skinFallback));
        var secondary = normalizeHexInput($('#brand_secondary').val(), $('#brand_secondary_picker').val() || '#09243c');
        var $preview = $('#settings-brand-preview');
        if (!$preview.length) return;
        $preview.css({
            '--preview-primary': primary,
            '--preview-secondary': secondary
        });
        $('#brand_primary_picker').val(primary);
        $('#brand_secondary_picker').val(secondary);
    }

    $('#brand_primary, #brand_secondary, #brand_primary_picker, #brand_secondary_picker').on('input change', syncBrandPreview);
    $('.settings-skin-picker li').on('click', function () {
        setTimeout(syncBrandPreview, 0);
    });
    syncBrandPreview();

    if (typeof initSparkline === 'function') {
        initSparkline();
    }
});
</script>
@stack('settings-scripts')
@endpush
