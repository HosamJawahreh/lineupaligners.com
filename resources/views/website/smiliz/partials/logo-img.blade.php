@php
    $logoFallback = asset('assets/images/logo.svg');
    $logoClass = trim($class ?? 'logo-img');
    $logoWidth = (int) ($width ?? 180);
    $logoHeight = (int) ($height ?? 96);
@endphp
<img class="{{ $logoClass }}"
     src="{{ $logoUrl }}"
     alt="{{ $projectName }}"
     width="{{ $logoWidth }}"
     height="{{ $logoHeight }}"
     decoding="async"
     onerror="this.onerror=null;this.src='{{ $logoFallback }}';">
