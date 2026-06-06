@php
    $localeService = $websiteLocaleService ?? app(\App\Services\WebsiteLocale::class);
    $localeSwitcherItems = collect($websiteLocales ?? $localeService->enabled())
        ->mapWithKeys(fn ($meta, $code) => [
            $code => array_merge($meta, ['url' => $localeService->switchUrl($code)]),
        ])
        ->all();
@endphp
@if(count($localeSwitcherItems) > 1)
@include('partials.locale-switcher-pill', [
    'items' => $localeSwitcherItems,
    'active' => $websiteLocale ?? 'en',
    'ariaLabel' => __('website.language'),
    'class' => 'lineup-lang-switcher--header-bar',
])
@endif
