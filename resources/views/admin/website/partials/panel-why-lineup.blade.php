<section class="wm-panel d-none" id="wm-panel-why-lineup">
    <header class="wm-panel__head">
        <div>
            <h3 class="wm-panel__title">Why LINEUP</h3>
            <p class="wm-panel__desc">Homepage platform section — heading, intro, and feature cards shown in the services carousel.</p>
        </div>
        @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'services', 'sectionLabel' => 'Why LINEUP section'])
    </header>
    <div class="wm-panel__body">
        <div class="wm-block">
            <h4 class="wm-block__title">Section header</h4>
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="platform_subtitle" class="form-control wm-input" value="{{ old('platform_subtitle', $content['platform']['subtitle']) }}" placeholder="Section label">
                </div>
                <div class="col-md-8">
                    <input type="text" name="platform_title" class="form-control wm-input" value="{{ old('platform_title', $content['platform']['title']) }}" placeholder="Section title">
                </div>
            </div>
            <textarea name="platform_intro" class="form-control wm-input m-t-10" rows="2" placeholder="Short introduction (optional)">{{ old('platform_intro', $content['platform']['intro']) }}</textarea>
        </div>

        <div class="wm-block">
            <h4 class="wm-block__title">Section button <span class="text-muted">(optional)</span></h4>
            <p class="wm-hint">Shown below the cards when filled in.</p>
            <div class="row">
                <div class="col-md-5">
                    <input type="text" name="platform_cta_label" class="form-control wm-input" value="{{ old('platform_cta_label', $content['platform']['cta_label']) }}" placeholder="e.g. View all services">
                </div>
                <div class="col-md-7">
                    <input type="text" name="platform_cta_url" class="form-control wm-input" value="{{ old('platform_cta_url', $content['platform']['cta_url']) }}" placeholder="Link URL (optional)">
                </div>
            </div>
            @if($editLocale !== 'en')
            <p class="wm-hint wm-hint--info m-b-0 m-t-10"><i class="zmdi zmdi-info-outline"></i> Button URL is shared; label follows the language you are editing.</p>
            @endif
        </div>

        <div class="wm-block">
            <h4 class="wm-block__title">Feature cards</h4>
            <p class="wm-hint">These cards appear on the homepage carousel and on the public Services listing page.</p>
            @if($editLocale !== 'en')
            <p class="wm-hint wm-hint--info m-b-12"><i class="zmdi zmdi-info-outline"></i> Photos, slugs, and custom links are shared across languages.</p>
            @endif
            <div id="website-features-list">
                @foreach(old('features', $content['features']) as $i => $feature)
                <div class="wm-feature-card website-repeatable__row">
                    <input type="hidden" name="features[{{ $i }}][image]" value="{{ $feature['image'] ?? '' }}">
                    @include('admin.website.partials.section-image-field', [
                        'inputName' => "features[{$i}][image_file]",
                        'currentUrl' => $websiteContent->featureImageUrl($feature, $i),
                        'removeName' => !empty($feature['image']) ? "features[{$i}][remove_image]" : null,
                        'previewId' => 'feature-preview-'.$i,
                        'compact' => true,
                    ])
                    <div class="wm-feature-card__fields">
                        @include('admin.website.partials.icon-select', [
                            'name' => "features[{$i}][icon]",
                            'selected' => $feature['icon'] ?? '',
                            'iconOptions' => $iconOptions,
                            'smilizIconOptions' => $smilizIconOptions ?? [],
                        ])
                        <input type="text" name="features[{{ $i }}][title]" class="form-control wm-input" value="{{ $feature['title'] ?? '' }}" placeholder="Card title">
                        <input type="text" name="features[{{ $i }}][description]" class="form-control wm-input" value="{{ $feature['description'] ?? '' }}" placeholder="Short description">
                        <div class="row">
                            <div class="col-md-5">
                                <input type="text" name="features[{{ $i }}][button_label]" class="form-control wm-input" value="{{ $feature['button_label'] ?? '' }}" placeholder="Button text (optional)">
                            </div>
                            <div class="col-md-7">
                                <input type="text" name="features[{{ $i }}][link_url]" class="form-control wm-input" value="{{ $feature['link_url'] ?? '' }}" placeholder="Custom link (optional)">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-simple btn-danger website-remove-row" title="Remove"><i class="zmdi zmdi-close"></i></button>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-default btn-round m-t-10" id="website-add-feature"><i class="zmdi zmdi-plus"></i> Add card</button>
            <p class="wm-hint m-b-0 m-t-10">To edit each card’s full service detail page, open <strong>Services</strong> in the sidebar.</p>
        </div>
    </div>
</section>
