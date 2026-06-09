@php
    $linkClass = trim('lineup-nav-link '.($class ?? ''));
    $iconClass = trim((string) ($icon ?? ''));
    if ($iconClass !== '' && str_starts_with($iconClass, 'pbmit-smiliz-icon-') && ! str_contains($iconClass, 'pbmit-smiliz-icon ')) {
        $iconClass = 'pbmit-smiliz-icon '.$iconClass;
    }
@endphp
<a href="{{ $url }}" class="{{ $linkClass }}" @foreach($attributes ?? [] as $attr => $value) {{ $attr }}="{{ $value }}" @endforeach>
    @if($iconClass !== '')
    <span class="lineup-nav-link__icon" aria-hidden="true"><i class="{{ $iconClass }}"></i></span>
    @endif
    <span class="lineup-nav-link__text">{{ $label }}</span>
</a>
