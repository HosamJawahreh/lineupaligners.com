@php
    $uploadId = $uploadId ?? 'case-photos';
@endphp
<div class="case-photos-upload-block">
    <label class="case-photos-upload-block__label">
        Photos <span class="text-success small">optional — multiple</span>
    </label>
    <div class="case-photos-dropzone" id="{{ $uploadId }}-dropzone" data-photos-dropzone>
        <i class="zmdi zmdi-camera"></i>
        <p class="m-b-5">Drop images here or click to browse</p>
        <small class="text-muted">Max 100MB each — PNG, JPG, JPEG, WebP</small>
        <input type="file" name="photos[]" id="{{ $uploadId }}-input" multiple accept="image/jpeg,image/png,image/webp" data-photos-input>
    </div>
    <div id="{{ $uploadId }}-preview" class="case-photos-preview m-t-10" data-photos-preview></div>
</div>
