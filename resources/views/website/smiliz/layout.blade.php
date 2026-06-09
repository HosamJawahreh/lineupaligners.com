@php
    $scriptTierSection = trim($__env->yieldContent('smiliz-script-tier'));
    $smilizScriptTier = $scriptTierSection !== '' ? $scriptTierSection : ($smilizScriptTier ?? 'full');
@endphp
<!doctype html>

<html class="no-js" lang="{{ $websiteLocale ?? 'en' }}" dir="{{ $websiteDir ?? 'ltr' }}">

<head>

    <meta charset="utf-8">

    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $content['seo']['meta_title'] ?? $projectName)</title>

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    @include('website.smiliz.partials.seo-meta')

    @if($isPreview ?? false)

    <meta name="robots" content="noindex, nofollow">

    @endif

    @include('layouts.partials.favicon')

    @php($websiteFont = \App\Models\Setting::dashboardFont())

    @if(! empty($websiteFont['google_url']))

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="{{ $websiteFont['google_url'] }}" rel="stylesheet">

    @endif

    @if(($websiteDir ?? 'ltr') === 'rtl')
    <link rel="stylesheet" href="{{ asset('assets/smiliz/css/bootstrap.rtl.min.css') }}">
    @else
    <link rel="stylesheet" href="{{ asset('assets/smiliz/css/bootstrap.min.css') }}">
    @endif

    <link rel="stylesheet" href="{{ asset('assets/smiliz/css/fontawesome.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/smiliz/fonts/pbmit-smiliz-icon/pbmit_smiliz.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/smiliz/css/pbminfotech-base-icons.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/smiliz/css/themify-icons.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/smiliz/css/swiper.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/smiliz/css/magnific-popup.css') }}">

    @if(($smilizScriptTier ?? 'full') === 'full')

    <link rel="stylesheet" href="{{ asset('assets/smiliz/css/aos.css') }}">

    @endif

    <link rel="stylesheet" href="{{ asset('assets/smiliz/css/shortcode.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/smiliz/css/base.css') }}?v=2">

    <link rel="stylesheet" href="{{ asset('assets/smiliz/css/style.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/smiliz/css/responsive.css') }}">

    @if(($smilizScriptTier ?? 'full') === 'full' || trim($__env->yieldContent('needs-before-after')) === '1')

    <link rel="stylesheet" href="{{ asset('assets/smiliz/css/twentytwenty.css') }}">

    @endif

    <link rel="stylesheet" href="{{ asset('assets/css/lineup-lang-switcher.css') }}?v=5">

    <link rel="stylesheet" href="{{ asset('assets/css/lineup-performance.css') }}?v=1">

    <link rel="stylesheet" href="{{ asset('assets/css/lineup-smiliz-overrides.css') }}?v=84">

    <link rel="stylesheet" href="{{ asset('assets/css/lineup-brand-system.css') }}?v=3">

    <link rel="stylesheet" href="{{ asset('assets/css/lineup-loader.css') }}?v=3">

    @include('layouts.partials.brand-css-vars')

    @if(($websiteDir ?? 'ltr') === 'rtl')

    <link rel="stylesheet" href="{{ asset('assets/css/lineup-smiliz-rtl.css') }}?v=5">

    @endif

    @stack('styles')

</head>

<body class="lineup-smiliz-site @if(($websiteDir ?? 'ltr') === 'rtl') lineup-smiliz-site--rtl @endif">

    <div class="pbmit-mobile-menu-bg lineup-mobile-menu-backdrop" aria-hidden="true"></div>

    <a href="#page" class="lineup-skip-link">{{ __('website.skip_to_content') }}</a>

    @include('layouts.partials.lineup-page-loader')



    @if($isPreview ?? false)

    <div class="lineup-smiliz-preview-bar">

        <span>{{ __('website.preview_bar') }}@if($portfolioUsesDemo ?? false) {{ __('website.preview_demo_cases') }}@endif</span>

        <a href="{{ route('admin.website.index', ['section' => 'case-studies']) }}">{{ __('website.manage_cases') }}</a>

    </div>

    @endif



    <main class="page-wrapper" id="page" tabindex="-1">

        @yield('smiliz-body')

    </main>



    <button type="button" class="pbmit-backtotop lineup-back-to-top" aria-label="{{ __('website.back_to_top') }}">

        <div class="pbmit-arrow"><i class="pbmit-base-icon-up-open-big"></i></div>

        <div class="pbmit-hover-arrow"><i class="pbmit-base-icon-up-open-big"></i></div>

    </button>



    <script>

    window.lineupSmilizConfig = {

        inquiryUrl: @json(route(app(\App\Services\WebsiteLocale::class)->routeName('website.inquiry.store'))),

        successMessage: @json(__('website.inquiry_success')),

        errorMessage: @json(__('website.inquiry_error')),

        requiredMessage: @json(__('website.inquiry_required')),

        beforeLabel: @json(__('website.before')),

        afterLabel: @json(__('website.after')),

    };

    </script>

    @include('website.smiliz.partials.scripts-'.(($smilizScriptTier ?? 'full') === 'core' ? 'core' : 'full'))

    @if(trim($__env->yieldContent('needs-before-after')) === '1')
    @include('website.smiliz.partials.scripts-before-after')
    @endif

    @if(($websiteDir ?? 'ltr') === 'rtl')
    <script src="{{ asset('assets/js/lineup-smiliz-rtl.js') }}?v=1" defer></script>
    @endif

    @stack('scripts')

</body>

</html>


