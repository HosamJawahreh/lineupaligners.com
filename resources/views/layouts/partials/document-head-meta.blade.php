@php
    $brandName = $brandName ?? ($projectName ?? config('settings.brand_name', config('app.name', 'Lineup Aligner')));
    $pageTitle = trim($__env->yieldContent('title')) ?: 'Dashboard';
    $metaDescription = trim($__env->yieldContent('meta_description')) ?: ($brandName.' — clear aligner case management for clinics and doctors.');
@endphp
<title>{{ $pageTitle }} | {{ $brandName }}</title>
<meta name="description" content="{{ $metaDescription }}">
