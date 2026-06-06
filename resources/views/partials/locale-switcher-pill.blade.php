@if(count($items ?? []) > 1)
<div class="lineup-lang-switcher" aria-label="{{ $ariaLabel ?? 'Language' }}">
    @foreach($items as $code => $item)
    <a href="{{ $item['url'] }}"
       @class(['lineup-lang-switcher__btn', 'is-active' => ($active ?? null) === $code])
       hreflang="{{ $code }}"
       lang="{{ $code }}">
        <span class="lineup-lang-switcher__flag" aria-hidden="true">{{ $item['flag'] ?? '' }}</span>
        <span class="lineup-lang-switcher__label">{{ $item['native'] ?? $code }}</span>
    </a>
    @endforeach
</div>
@endif
