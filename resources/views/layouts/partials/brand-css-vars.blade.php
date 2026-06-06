@php
    $brand = $brandColors ?? app(\App\Services\BrandColors::class)->tokens();
    $siteFont = \App\Models\Setting::dashboardFont();
@endphp
<style id="lineup-brand-vars">
:root {
    --lineup-skin: {{ $brand['primary'] }};
    --lineup-brand: {{ $brand['primary'] }};
    --lineup-brand-dark: {{ $brand['primary_dark'] }};
    --lineup-brand-soft: {{ $brand['primary_soft'] }};
    --lineup-brand-secondary: {{ $brand['secondary'] }};
    --lineup-brand-secondary-dark: {{ $brand['secondary_dark'] }};
    --lineup-brand-secondary-soft: {{ $brand['secondary_soft'] }};
    --lineup-brand-primary-rgb: {{ $brand['primary_rgb'] }};
    --lineup-brand-secondary-rgb: {{ $brand['secondary_rgb'] }};
    --lineup-font-sans: {!! $siteFont['stack'] !!};
    --lineup-font-display: var(--lineup-font-sans);
}

.lineup-smiliz-site {
    --pbmit-global-color: var(--lineup-brand);
    --pbmit-global-color-rgb: var(--lineup-brand-primary-rgb);
    --pbmit-secondary-color: var(--lineup-brand-secondary);
    --pbmit-secondary-color-rgb: var(--lineup-brand-secondary-rgb);
    --pbmit-blackish-color: var(--lineup-brand-secondary);
    --pbmit-blackish-color-rgb: var(--lineup-brand-secondary-rgb);
    --pbmit-body-typography-font-family: var(--lineup-font-sans);
    --pbmit-heading-typography-font-family: var(--lineup-font-display);
    --pbmit-btn-typography-font-family: var(--lineup-font-display);
}
</style>
