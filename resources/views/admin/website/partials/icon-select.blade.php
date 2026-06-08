@php
    $name = $name ?? 'icon';
    $selected = $selected ?? '';
    $iconOptions = $iconOptions ?? [];
    $smilizIconOptions = $smilizIconOptions ?? [];
@endphp
<select name="{{ $name }}" class="form-control wm-input">
    @if(count($smilizIconOptions) > 0)
    <optgroup label="Smiliz dental icons">
        @foreach($smilizIconOptions as $icon)
        <option value="{{ $icon }}" @selected($selected === $icon)>{{ $icon }}</option>
        @endforeach
    </optgroup>
    @endif
    @if(count($iconOptions) > 0)
    <optgroup label="Material Design icons">
        @foreach($iconOptions as $icon)
        <option value="{{ $icon }}" @selected($selected === $icon)>{{ $icon }}</option>
        @endforeach
    </optgroup>
    @endif
</select>
