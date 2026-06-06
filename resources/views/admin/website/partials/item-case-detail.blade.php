@php
    $detail = $detail ?? [];
    $detailDefaults = config('website.default_case_study_page', []);
    $detailUrl = !empty($slug ?? null) ? $websiteContent->caseStudyDetailUrl(['slug' => $slug], $editLocale ?? null) : null;
@endphp
<details class="wm-item-detail" @if($open ?? false) open @endif>
    <summary class="wm-item-detail__summary">
        <span>Full case study page</span>
        @if($detailUrl)
        <a href="{{ $detailUrl }}" class="wm-item-detail__link" target="_blank" rel="noopener" onclick="event.stopPropagation();">Preview</a>
        @endif
    </summary>
    <div class="wm-item-detail__body">
        <input type="text" name="slug" class="form-control wm-input m-b-10" value="{{ $slug ?? '' }}" placeholder="URL slug (auto from title if empty)">
        <input type="text" name="detail_title" class="form-control wm-input m-b-10" value="{{ $detail['title'] ?? '' }}" placeholder="Detail page title (defaults to case title above)">
        <div class="row">
            <div class="col-md-6">
                <input type="text" name="detail_summary_title" class="form-control wm-input m-b-10" value="{{ $detail['summary_title'] ?? '' }}" placeholder="Case summary (heading)">
                <textarea name="detail_intro" class="form-control wm-input m-b-10" rows="2" placeholder="Case summary intro (defaults to listing summary)">{{ $detail['intro'] ?? '' }}</textarea>
                <textarea name="detail_body" class="form-control wm-input m-b-10" rows="2" placeholder="Case summary body">{{ $detail['body'] ?? '' }}</textarea>
            </div>
            <div class="col-md-6">
                <input type="text" name="detail_what_we_did_title" class="form-control wm-input m-b-10" value="{{ $detail['what_we_did_title'] ?? '' }}" placeholder="Treatment approach (heading)">
                <textarea name="detail_what_we_did_body" class="form-control wm-input m-b-10" rows="2" placeholder="Treatment approach body">{{ $detail['what_we_did_body'] ?? '' }}</textarea>
                <textarea name="detail_sidebar_intro" class="form-control wm-input m-b-10" rows="2" placeholder="Case info sidebar intro">{{ $detail['sidebar_intro'] ?? '' }}</textarea>
            </div>
        </div>
        <div class="row m-b-10">
            <div class="col-md-3">
                <input type="text" name="detail_client" class="form-control wm-input" value="{{ $detail['client'] ?? '' }}" placeholder="Clinic / client">
            </div>
            <div class="col-md-3">
                <input type="text" name="detail_category" class="form-control wm-input" value="{{ $detail['category'] ?? '' }}" placeholder="Category">
            </div>
            <div class="col-md-3">
                <input type="text" name="detail_date" class="form-control wm-input" value="{{ $detail['date'] ?? '' }}" placeholder="Date">
            </div>
            <div class="col-md-3">
                <input type="text" name="detail_location" class="form-control wm-input" value="{{ $detail['location'] ?? '' }}" placeholder="Location">
            </div>
        </div>
        <p class="wm-hint m-b-10">Before/after photos above are used on the detail page. Add optional extra detail photos below.</p>
        <div class="row">
            <div class="col-md-6">
                <label class="wm-label">Detail photo 1</label>
                <input type="hidden" name="detail_image1" value="{{ $detail['detail_image1'] ?? '' }}">
                @include('admin.website.partials.section-image-field', [
                    'inputName' => 'detail_image1_file',
                    'currentUrl' => $websiteContent->pageImageUrl($detail['detail_image1'] ?? null, $detailDefaults['detail_image1']),
                    'removeName' => !empty($detail['detail_image1']) ? 'detail_image1_remove' : null,
                    'previewId' => 'case-detail1-preview',
                    'compact' => true,
                ])
            </div>
            <div class="col-md-6">
                <label class="wm-label">Detail photo 2</label>
                <input type="hidden" name="detail_image2" value="{{ $detail['detail_image2'] ?? '' }}">
                @include('admin.website.partials.section-image-field', [
                    'inputName' => 'detail_image2_file',
                    'currentUrl' => $websiteContent->pageImageUrl($detail['detail_image2'] ?? null, $detailDefaults['detail_image2']),
                    'removeName' => !empty($detail['detail_image2']) ? 'detail_image2_remove' : null,
                    'previewId' => 'case-detail2-preview',
                    'compact' => true,
                ])
            </div>
        </div>
    </div>
</details>
