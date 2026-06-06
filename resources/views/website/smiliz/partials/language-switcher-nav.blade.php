@if(count($websiteLocales ?? []) > 1)
@php
    $localeSwitcherItems = collect($websiteLocales)->mapWithKeys(fn ($meta, $code) => [
        $code => array_merge($meta, ['url' => $websiteLocaleService->switchUrl($code)]),
    ])->all();
@endphp
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
