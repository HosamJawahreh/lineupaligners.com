@php
    $detail = $item['detail'] ?? [];
    $detailDefaults = config('website.default_blog_page', []);
    $detailUrl = !empty($item['slug'] ?? null) ? $websiteContent->blogPostUrl($item, $editLocale ?? null) : null;
@endphp
<details class="wm-item-detail">
    <summary class="wm-item-detail__summary">
        <span>Article content</span>
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
        <input type="text" name="{{ $prefix }}[detail][title]" class="form-control wm-input m-b-10" value="{{ $detail['title'] ?? '' }}" placeholder="Article title (defaults to card title)">
        <div class="row m-b-10">
            <div class="col-md-4">
                <input type="text" name="{{ $prefix }}[detail][category]" class="form-control wm-input" value="{{ $detail['category'] ?? ($item['category'] ?? '') }}" placeholder="Category">
            </div>
            <div class="col-md-4">
                <input type="text" name="{{ $prefix }}[detail][date]" class="form-control wm-input" value="{{ $detail['date'] ?? ($item['date'] ?? '') }}" placeholder="Date">
            </div>
            <div class="col-md-4">
                <input type="text" name="{{ $prefix }}[detail][author]" class="form-control wm-input" value="{{ $detail['author'] ?? '' }}" placeholder="Author">
            </div>
        </div>
        <textarea name="{{ $prefix }}[detail][intro]" class="form-control wm-input m-b-10" rows="3" placeholder="Opening paragraph">{{ $detail['intro'] ?? '' }}</textarea>
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
        <div class="row m-b-10">
            <div class="col-md-8">
                <textarea name="{{ $prefix }}[detail][quote]" class="form-control wm-input" rows="2" placeholder="Pull quote">{{ $detail['quote'] ?? '' }}</textarea>
            </div>
            <div class="col-md-4">
                <input type="text" name="{{ $prefix }}[detail][quote_author]" class="form-control wm-input m-b-10" value="{{ $detail['quote_author'] ?? '' }}" placeholder="Quote author">
                <input type="text" name="{{ $prefix }}[detail][tags]" class="form-control wm-input" value="{{ implode(', ', $detail['tags'] ?? []) }}" placeholder="Tags (comma separated)">
            </div>
        </div>
        <textarea name="{{ $prefix }}[detail][author_bio]" class="form-control wm-input m-b-10" rows="2" placeholder="Author bio">{{ $detail['author_bio'] ?? '' }}</textarea>
        @if($editLocale !== 'en')
        <p class="wm-hint wm-hint--info m-b-10"><i class="zmdi zmdi-info-outline"></i> Article photo is shared across languages.</p>
        @endif
        <input type="hidden" name="{{ $prefix }}[detail][image]" value="{{ $detail['image'] ?? '' }}">
        @include('admin.website.partials.section-image-field', [
            'inputName' => "{$prefix}[detail][image_file]",
            'currentUrl' => $websiteContent->pageImageUrl($detail['image'] ?? null, $detailDefaults['image'] ?? 'images/blog/blog-img-01.jpg'),
            'removeName' => !empty($detail['image']) ? "{$prefix}[detail][remove_image]" : null,
            'previewId' => $previewId ?? ('blog-detail-preview-'.$index),
            'compact' => true,
        ])
    </div>
</details>
