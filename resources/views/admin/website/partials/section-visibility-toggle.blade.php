@php
    $sectionKey = $sectionKey ?? '';
    $sectionLabel = $sectionLabel ?? Str::headline(str_replace('_', ' ', $sectionKey));
    $variant = $variant ?? 'head';
    $checked = old("sections.$sectionKey", $content['sections'][$sectionKey] ?? ($defaultSections[$sectionKey] ?? true));
@endphp
@if($variant === 'grid')
<label class="wm-section-toggle">
    <input type="hidden" name="sections[{{ $sectionKey }}]" value="0">
    <input type="checkbox" name="sections[{{ $sectionKey }}]" value="1" @checked($checked)>
    <span>{{ $sectionLabel }}</span>
</label>
@else
<div class="wm-section-visibility">
    <label class="wm-section-toggle wm-section-toggle--inline">
        <input type="hidden" name="sections[{{ $sectionKey }}]" value="0">
        <input type="checkbox" name="sections[{{ $sectionKey }}]" value="1" @checked($checked)>
        <span>Show <strong>{{ $sectionLabel }}</strong> on homepage</span>
    </label>
</div>
@endif
