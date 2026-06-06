@if(count($websiteLocales ?? []) > 1)
@php
    $localeSwitcherItems = collect($websiteLocales)->mapWithKeys(fn ($meta, $code) => [
        $code => array_merge($meta, ['url' => $websiteLocaleService->switchUrl($code)]),
    ])->all();
@endphp
@include('partials.locale-switcher-pill', [
    'items' => $localeSwitcherItems,
    'active' => $websiteLocale ?? 'en',
    'ariaLabel' => __('website.language'),
])
@endif
