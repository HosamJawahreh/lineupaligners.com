@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/settings-layout.css') }}">
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

    if (typeof initSparkline === 'function') {
        initSparkline();
    }
});
</script>
@stack('settings-scripts')
@endpush
