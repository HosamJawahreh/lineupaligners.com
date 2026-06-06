@php
    $previewId = $previewId ?? ('img-preview-'.md5($inputName ?? uniqid()));
    $accept = $accept ?? 'image/jpeg,image/png,image/webp';
@endphp
<div class="wm-section-media @if(!empty($compact)) wm-section-media--compact @endif @if(!empty($logoField)) wm-section-media--logo @endif">
    <div class="wm-section-media__preview" id="{{ $previewId }}">
        @if(!empty($currentUrl))
        <img src="{{ $currentUrl }}" alt="">
        @else
        <span class="wm-section-media__empty"><i class="zmdi zmdi-image"></i></span>
        @endif
    </div>
    <div class="wm-section-media__controls">
        @if(!empty($label))
        <label class="wm-label">{{ $label }}</label>
        @endif
        <input type="file"
               name="{{ $inputName }}"
               class="form-control wm-input wm-image-input"
               accept="{{ $accept }}"
               data-preview="{{ $previewId }}">
        @if(!empty($hint))
        <p class="wm-hint m-b-0">{{ $hint }}</p>
        @endif
        @if(!empty($removeName) && !empty($currentUrl))
        <label class="wm-check m-t-5"><input type="checkbox" name="{{ $removeName }}" value="1"> Use default photo</label>
        @endif
    </div>
</div>
