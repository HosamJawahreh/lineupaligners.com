@php
    $linkClass = trim('lineup-nav-link '.($class ?? ''));
@endphp
<a href="{{ $url }}" class="{{ $linkClass }}" @foreach($attributes ?? [] as $attr => $value) {{ $attr }}="{{ $value }}" @endforeach>
    @if(filled($icon ?? ''))
    <span class="lineup-nav-link__icon" aria-hidden="true"><i class="{{ $icon }}"></i></span>
    @endif
    <span class="lineup-nav-link__text">{{ $label }}</span>
</a>
