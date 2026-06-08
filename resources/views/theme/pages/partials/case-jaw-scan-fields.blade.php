@php
    $upperInputId = $upperInputId ?? 'upper-jaw-scan';
    $lowerInputId = $lowerInputId ?? 'lower-jaw-scan';
    $upperInputName = $upperInputName ?? 'upper_jaw_scan';
    $lowerInputName = $lowerInputName ?? 'lower_jaw_scan';
    $upperPlaceholder = asset('assets/images/placeholders/uppper-paceholder.jpeg');
    $lowerPlaceholder = asset('assets/images/placeholders/lower-paceholder.jpeg');
@endphp

<div class="case-jaw-scan-fields">
    <div class="scan-upload-box scan-upload-box--upper">
        <label for="{{ $upperInputId }}">
            Upper jaw 3D file <span class="case-modification-card__optional">optional</span>
        </label>
        <div class="scan-upload-box__visual" aria-hidden="true">
            <img src="{{ $upperPlaceholder }}" alt="" width="160" height="100">
        </div>
        <input type="file"
               id="{{ $upperInputId }}"
               name="{{ $upperInputName }}"
               accept=".stl,.obj,.ply,.zip">
        <span class="case-modification-card__hint">STL, OBJ, PLY, or ZIP — upload when you have a new upper scan.</span>
    </div>

    <div class="scan-upload-box scan-upload-box--lower">
        <label for="{{ $lowerInputId }}">
            Lower jaw 3D file <span class="case-modification-card__optional">optional</span>
        </label>
        <div class="scan-upload-box__visual" aria-hidden="true">
            <img src="{{ $lowerPlaceholder }}" alt="" width="160" height="100">
        </div>
        <input type="file"
               id="{{ $lowerInputId }}"
               name="{{ $lowerInputName }}"
               accept=".stl,.obj,.ply,.zip">
        <span class="case-modification-card__hint">STL, OBJ, PLY, or ZIP — upload when you have a new lower scan.</span>
    </div>
</div>
