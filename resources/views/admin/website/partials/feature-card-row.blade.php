@php
    $rowIndex = $i ?? 0;
    $feature = $feature ?? [];
    $detailUrl = ! empty($feature['slug'] ?? null) ? $websiteContent->serviceDetailUrl($feature, $editLocale ?? null) : null;
@endphp
<div class="wm-feature-card wm-feature-card--with-detail website-repeatable__row">
    <div class="wm-feature-card__top">
        <input type="hidden" name="features[{{ $rowIndex }}][image]" value="{{ $feature['image'] ?? '' }}">
        @include('admin.website.partials.section-image-field', [
            'inputName' => "features[{$rowIndex}][image_file]",
            'currentUrl' => is_numeric($rowIndex) ? $websiteContent->featureImageUrl($feature, (int) $rowIndex) : null,
            'removeName' => ! empty($feature['image']) ? "features[{$rowIndex}][remove_image]" : null,
            'previewId' => 'feature-preview-'.$rowIndex,
            'compact' => true,
        ])
        <div class="wm-feature-card__fields">
            @include('admin.website.partials.icon-select', [
                'name' => "features[{$rowIndex}][icon]",
                'selected' => $feature['icon'] ?? '',
                'iconOptions' => $iconOptions,
                'smilizIconOptions' => $smilizIconOptions ?? [],
            ])
            <input type="text" name="features[{{ $rowIndex }}][title]" class="form-control wm-input" value="{{ $feature['title'] ?? '' }}" placeholder="Card title">
            <input type="text" name="features[{{ $rowIndex }}][description]" class="form-control wm-input" value="{{ $feature['description'] ?? '' }}" placeholder="Short description">
            <div class="row">
                <div class="col-md-5">
                    <input type="text" name="features[{{ $rowIndex }}][button_label]" class="form-control wm-input" value="{{ $feature['button_label'] ?? '' }}" placeholder="Button text (optional)">
                </div>
                <div class="col-md-7">
                    <input type="text" name="features[{{ $rowIndex }}][link_url]" class="form-control wm-input" value="{{ $feature['link_url'] ?? '' }}" placeholder="Custom link (optional)">
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-simple btn-danger website-remove-row" title="Remove"><i class="zmdi zmdi-close"></i></button>
    </div>
    <div class="wm-feature-card__detail">
        @if($detailUrl)
        <a href="{{ $detailUrl }}" class="wm-item-detail__link wm-feature-card__preview" target="_blank" rel="noopener">Preview detail page</a>
        @endif
        @include('admin.website.partials.item-service-detail', [
            'prefix' => "features[{$rowIndex}]",
            'item' => $feature,
            'index' => $rowIndex,
            'previewId' => 'service-detail-preview-'.$rowIndex,
        ])
    </div>
</div>
