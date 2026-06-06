<section class="wm-panel d-none" id="wm-panel-services">
    <header class="wm-panel__head">
        <div>
            <h3 class="wm-panel__title">Services</h3>
            <p class="wm-panel__desc">Edit the full detail page for each service. Homepage cards are managed under <strong>Homepage sections → Why LINEUP</strong>.</p>
        </div>
    </header>
    <div class="wm-panel__body">
        <div class="wm-block wm-block--muted">
            <p class="wm-hint m-b-0">
                <i class="zmdi zmdi-info-outline"></i>
                To change card titles, photos, or the homepage section heading, use
                <a href="#wm-panel-why-lineup" class="wm-goto-section" data-wm-section="why-lineup">Why LINEUP</a>.
            </p>
        </div>

        <div class="wm-block">
            <h4 class="wm-block__title">Service detail pages</h4>
            @if($editLocale !== 'en')
            <p class="wm-hint wm-hint--info m-b-12"><i class="zmdi zmdi-info-outline"></i> URL slugs and hero photos are shared across languages.</p>
            @endif
            <div id="website-service-details-list">
                @foreach(old('features', $content['features']) as $i => $feature)
                <div class="wm-service-detail-card">
                    <div class="wm-service-detail-card__head">
                        <strong>{{ $feature['title'] ?? 'Service '.($i + 1) }}</strong>
                        @if(!empty($feature['slug'] ?? null))
                        <a href="{{ $websiteContent->serviceDetailUrl($feature, $editLocale) }}" class="wm-item-detail__link" target="_blank" rel="noopener">Preview page</a>
                        @endif
                    </div>
                    @include('admin.website.partials.item-service-detail', [
                        'prefix' => "features[{$i}]",
                        'item' => $feature,
                        'index' => $i,
                        'previewId' => 'service-detail-preview-'.$i,
                    ])
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
