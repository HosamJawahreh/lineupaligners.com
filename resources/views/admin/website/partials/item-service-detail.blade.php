@php
    $detail = $item['detail'] ?? [];
    $detailDefaults = config('website.default_service_page', []);
    $detailUrl = !empty($item['slug'] ?? null) ? $websiteContent->serviceDetailUrl($item, $editLocale ?? null) : null;
@endphp
<details class="wm-item-detail">
    <summary class="wm-item-detail__summary">
        <span>Full service page</span>
        @if($detailUrl)
        <a href="{{ $detailUrl }}" class="wm-item-detail__link" target="_blank" rel="noopener" onclick="event.stopPropagation();">Preview</a>
        @endif
    </summary>
    <div class="wm-item-detail__body">
        @if($editLocale === 'en')
        <input type="text" name="{{ $prefix }}[slug]" class="form-control wm-input m-b-10" value="{{ $item['slug'] ?? '' }}" placeholder="URL slug (auto from title if empty)">
        @else
        <p class="wm-hint wm-hint--info m-b-10"><i class="zmdi zmdi-info-outline"></i> URL slug is shared across languages.</p>
        @endif
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="{{ $prefix }}[detail][eyebrow]" class="form-control wm-input m-b-10" value="{{ $detail['eyebrow'] ?? '' }}" placeholder="Page eyebrow">
            </div>
            <div class="col-md-8">
                <input type="text" name="{{ $prefix }}[detail][title]" class="form-control wm-input m-b-10" value="{{ $detail['title'] ?? '' }}" placeholder="Detail title (defaults to card title)">
            </div>
        </div>
        <textarea name="{{ $prefix }}[detail][intro]" class="form-control wm-input m-b-10" rows="2" placeholder="Opening paragraph">{{ $detail['intro'] ?? '' }}</textarea>
        <textarea name="{{ $prefix }}[detail][body]" class="form-control wm-input m-b-10" rows="2" placeholder="Second paragraph">{{ $detail['body'] ?? '' }}</textarea>
        <div class="row">
            <div class="col-md-6">
                <input type="text" name="{{ $prefix }}[detail][section2_title]" class="form-control wm-input m-b-10" value="{{ $detail['section2_title'] ?? '' }}" placeholder="Section 2 title">
                <textarea name="{{ $prefix }}[detail][section2_body]" class="form-control wm-input m-b-10" rows="2" placeholder="Section 2 body">{{ $detail['section2_body'] ?? '' }}</textarea>
            </div>
            <div class="col-md-6">
                <input type="text" name="{{ $prefix }}[detail][section3_title]" class="form-control wm-input m-b-10" value="{{ $detail['section3_title'] ?? '' }}" placeholder="Section 3 title">
                <textarea name="{{ $prefix }}[detail][section3_body]" class="form-control wm-input m-b-10" rows="2" placeholder="Section 3 body">{{ $detail['section3_body'] ?? '' }}</textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <input type="text" name="{{ $prefix }}[detail][sidebar_heading]" class="form-control wm-input m-b-10" value="{{ $detail['sidebar_heading'] ?? '' }}" placeholder="Sidebar heading">
                <textarea name="{{ $prefix }}[detail][sidebar_text]" class="form-control wm-input m-b-10" rows="2" placeholder="Sidebar text">{{ $detail['sidebar_text'] ?? '' }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="wm-label">Sidebar service list <span class="text-muted">(one per line)</span></label>
                <textarea name="{{ $prefix }}[detail][sidebar_services_text]" class="form-control wm-input m-b-10" rows="4" placeholder="Clear aligner planning">{{ old($prefix.'.detail.sidebar_services_text', implode("\n", $detail['sidebar_services'] ?? $detailDefaults['sidebar_services'] ?? [])) }}</textarea>
            </div>
        </div>
        @if($editLocale !== 'en')
        <p class="wm-hint wm-hint--info m-b-10"><i class="zmdi zmdi-info-outline"></i> Detail hero photo is shared across languages.</p>
        @endif
        <input type="hidden" name="{{ $prefix }}[detail][image]" value="{{ $detail['image'] ?? '' }}">
        @include('admin.website.partials.section-image-field', [
            'inputName' => "{$prefix}[detail][image_file]",
            'currentUrl' => $websiteContent->pageImageUrl($detail['image'] ?? null, $detailDefaults['image'] ?? 'images/service/service-single-01.jpg'),
            'removeName' => !empty($detail['image']) ? "{$prefix}[detail][remove_image]" : null,
            'previewId' => $previewId ?? ('service-detail-preview-'.$index),
            'compact' => true,
        ])
    </div>
</details>
