@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/settings-layout.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-lang-switcher.css') }}?v=3">
<link rel="stylesheet" href="{{ asset('assets/smiliz/fonts/pbmit-smiliz-icon/pbmit_smiliz.css') }}">
<link rel="stylesheet" href="{{ asset('assets/smiliz/css/pbminfotech-base-icons.css') }}">
<link rel="stylesheet" href="{{ asset('assets/smiliz/css/themify-icons.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lineup-website-admin.css') }}?v=28">
@endpush

@section('content')
<section class="content website-manage-page">
    <div class="block-header">
        <div class="row">
            <div class="col-lg-8 col-md-7 col-sm-12">
                <h2>@yield('website-heading', 'Manage Website')<small>@yield('website-subheading', 'Public marketing site for doctors & clinics')</small></h2>
            </div>
            <ul class="breadcrumb float-md-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="zmdi zmdi-home"></i></a></li>
                <li class="breadcrumb-item active">Website</li>
            </ul>
        </div>
    </div>

    <div class="container-fluid">
        @yield('website-content')
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/lineup-website-admin.js') }}?v=29"></script>
@stack('website-scripts')
@endpush
