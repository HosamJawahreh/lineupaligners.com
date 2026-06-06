<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('website.unpublished_title') }} — {{ $projectName }}</title>
    <meta name="description" content="{{ __('website.unpublished_body') }}">
    @php($websiteFont = \App\Models\Setting::dashboardFont())
    @if(! empty($websiteFont['google_url']))
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $websiteFont['google_url'] }}" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/css/lineup-public-website.css') }}?v=2">
    @include('layouts.partials.favicon')
    @include('layouts.partials.brand-css-vars')
</head>
<body class="lineup-public lineup-public--unpublished">
    <main class="lineup-public-draft">
        <img src="{{ $logoUrl }}" alt="{{ $projectName }}" width="64" height="64">
        <h1>{{ $projectName }}</h1>
        <p>{{ __('website.unpublished_intro') }}</p>
        <p class="lineup-public-draft__body">{{ __('website.unpublished_body') }}</p>
        <a href="{{ route('login') }}" class="lineup-public-btn">{{ __('website.unpublished_cta') }}</a>
    </main>
</body>
</html>
