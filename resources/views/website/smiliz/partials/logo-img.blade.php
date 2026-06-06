@php
    $logoFallback = \App\Models\Setting::defaultLogoAsset();
    $resolvedLogo = \App\Support\PublicStorageUrl::url(
        trim((string) \App\Models\Setting::get('logo', '')),
        $logoFallback
    ) ?? ($logoUrl ?? $logoFallback);
    $logoClass = trim($class ?? 'logo-img');
    $logoWidth = (int) ($width ?? 180);
    $logoHeight = (int) ($height ?? 96);
@endphp
<img class="{{ $logoClass }}"
     src="{{ $resolvedLogo }}"
     alt="{{ $projectName }}"
     width="{{ $logoWidth }}"
     height="{{ $logoHeight }}"
     decoding="async"
     onerror="this.onerror=null;this.src='{{ $logoFallback }}';">
