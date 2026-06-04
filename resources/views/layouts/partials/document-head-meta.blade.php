@php
    $brandName = $brandName ?? ($projectName ?? config('app.name', 'LineUp Aligners'));
    $pageTitle = trim($__env->yieldContent('title')) ?: 'Dashboard';
    $metaDescription = trim($__env->yieldContent('meta_description')) ?: ($brandName.' — clear aligner case management for clinics and doctors.');
@endphp
<title>{{ $pageTitle }} | {{ $brandName }}</title>
<meta name="description" content="{{ $metaDescription }}">
