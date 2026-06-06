@php
    $localeService = $websiteLocaleService ?? app(\App\Services\WebsiteLocale::class);
    $localeSwitcherItems = collect($websiteLocales ?? $localeService->enabled())
        ->mapWithKeys(fn ($meta, $code) => [
            $code => array_merge($meta, ['url' => $localeService->switchUrl($code)]),
        ])
        ->all();
@endphp
@if(count($localeSwitcherItems) > 1)
<li class="lineup-nav-lang-divider" aria-hidden="true"></li>
<li class="lineup-nav-lang-heading">
    <span>{{ __('website.language') }}</span>
</li>
@foreach($localeSwitcherItems as $code => $item)
<li @class(['lineup-nav-lang-item', 'active' => ($websiteLocale ?? 'en') === $code])>
    <a href="{{ $item['url'] }}"
       hreflang="{{ $code }}"
       lang="{{ $code }}"
       @if(($websiteLocale ?? 'en') === $code) aria-current="true" @endif>
        <span class="lineup-nav-lang-item__flag" aria-hidden="true">{{ $item['flag'] ?? '' }}</span>
        <span class="lineup-nav-lang-item__label">{{ $item['native'] ?? $code }}</span>
    </a>
</li>
@endforeach
@endif
