@php
    $inputId = $inputId ?? 'case-data-zip';
    $inputName = $inputName ?? 'case_data_zip';
    $label = $label ?? 'Case data archive';
@endphp

<div class="case-modification-card__upload-block case-modification-card__upload-block--zip">
    <label for="{{ $inputId }}">
        {{ $label }} <span class="case-modification-card__optional">optional</span>
    </label>
    <input type="file"
           id="{{ $inputId }}"
           name="{{ $inputName }}"
           accept=".zip,application/zip">
    <span class="case-modification-card__hint">ZIP file — max 100MB. Include scans, photos, or other case files in one archive.</span>
    @error($inputName)
    <span class="case-modification-card__field-error" role="alert">{{ $message }}</span>
    @enderror
</div>
