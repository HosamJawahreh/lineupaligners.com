@php
    $type = old(str_replace(['[', ']'], ['.', ''], $prefix).'type', $link['type'] ?? 'page');
@endphp
<div class="wm-nav-link-row wm-nav-link-row--compact" data-nav-link-row>
    <input type="text" name="{{ $prefix }}[label]" class="form-control wm-input wm-nav-link-row__label"
           value="{{ old(str_replace(['[', ']'], ['.', ''], $prefix).'label', $link['label'] ?? '') }}"
           placeholder="Link label">
    @unless($structuralLocked)
    <select name="{{ $prefix }}[type]" class="form-control wm-input wm-nav-link-type wm-nav-link-row__type">
        <option value="page" @selected($type === 'page')>Page</option>
        <option value="anchor" @selected($type === 'anchor')>Section</option>
        <option value="home" @selected($type === 'home')>Home</option>
        <option value="url" @selected($type === 'url')>URL</option>
    </select>
    <select name="{{ $prefix }}[page_key]" class="form-control wm-input wm-nav-link-row__page" data-show-when="page" @if($type !== 'page') style="display:none" @endif>
        <option value="">Select page…</option>
        @foreach($pageLinkOptions ?? [] as $key => $label)
        <option value="{{ $key }}" @selected(old(str_replace(['[', ']'], ['.', ''], $prefix).'page_key', $link['page_key'] ?? '') === $key)>{{ $label }}</option>
        @endforeach
    </select>
    <input type="text" name="{{ $prefix }}[url]" class="form-control wm-input wm-nav-link-row__url" data-show-when="anchor,home,url"
           value="{{ old(str_replace(['[', ']'], ['.', ''], $prefix).'url', $link['url'] ?? '') }}"
           placeholder="#cases or https://…"
           @if(! in_array($type, ['anchor', 'home', 'url'], true)) style="display:none" @endif>
    <button type="button" class="btn btn-sm btn-simple btn-danger website-remove-row" aria-label="Remove"><i class="zmdi zmdi-close"></i></button>
    @else
    <input type="hidden" name="{{ $prefix }}[type]" value="{{ $type }}">
    <input type="hidden" name="{{ $prefix }}[page_key]" value="{{ $link['page_key'] ?? '' }}">
    <input type="hidden" name="{{ $prefix }}[url]" value="{{ $link['url'] ?? '' }}">
    @endunless
</div>
