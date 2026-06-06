@php
    $seoTitle = trim($__env->yieldContent('title')) ?: ($seoTitle ?? $content['seo']['meta_title'] ?? $projectName);
    $seoDescription = trim($__env->yieldContent('meta_description')) ?: ($pageDescription ?? $content['seo']['meta_description'] ?? '');
    $seoDescription = \Illuminate\Support\Str::limit(strip_tags($seoDescription), 160, '');
    $seoCanonical = $canonicalUrl ?? url()->current();
    if (request()->has('preview')) {
        $seoCanonical = url()->to('/'.ltrim(request()->path(), '/'));
    }
    $seoImage = $seoImage ?? $heroImageUrl ?? $logoUrl ?? '';
    $seoType = $seoType ?? 'website';
    $localeService = app(\App\Services\WebsiteLocale::class);
    $enabledLocales = $websiteLocales ?? $localeService->enabled();
    $contact = $content['contact'] ?? [];
@endphp
@if(filled($seoDescription))
<meta name="description" content="{{ $seoDescription }}">
@endif
<link rel="canonical" href="{{ $seoCanonical }}">
@foreach($enabledLocales as $code => $meta)
<link rel="alternate" hreflang="{{ $code }}" href="{{ $localeService->switchUrl($code) }}">
@endforeach
@if(count($enabledLocales) > 1)
<link rel="alternate" hreflang="x-default" href="{{ $localeService->homeUrl(config('website-locales.default', 'en')) }}">
@endif
<meta property="og:locale" content="{{ str_replace('-', '_', $websiteLocale ?? 'en') }}">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:title" content="{{ $seoTitle }}">
@if(filled($seoDescription))
<meta property="og:description" content="{{ $seoDescription }}">
@endif
<meta property="og:url" content="{{ $seoCanonical }}">
<meta property="og:site_name" content="{{ $projectName }}">
@if(filled($seoImage))
<meta property="og:image" content="{{ $seoImage }}">
@endif
<meta name="twitter:card" content="{{ filled($seoImage) ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seoTitle }}">
@if(filled($seoDescription))
<meta name="twitter:description" content="{{ $seoDescription }}">
@endif
@if(filled($seoImage))
<meta name="twitter:image" content="{{ $seoImage }}">
@endif
<meta name="theme-color" content="{{ $content['brand']['primary'] ?? '#1a7fd4' }}">
@php
    $schema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'MedicalBusiness',
        'name' => $projectName,
        'url' => $websiteHomeUrl ?? $localeService->homeUrl(),
        'logo' => $logoUrl ?? null,
        'description' => filled($seoDescription) ? $seoDescription : null,
        'telephone' => filled($contact['phone'] ?? null) ? $contact['phone'] : null,
        'email' => filled($contact['email'] ?? null) ? $contact['email'] : null,
        'address' => filled($contact['address'] ?? null) ? [
            '@type' => 'PostalAddress',
            'streetAddress' => $contact['address'],
        ] : null,
    ]);
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
