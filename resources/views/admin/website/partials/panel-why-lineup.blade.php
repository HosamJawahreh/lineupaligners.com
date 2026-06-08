<section class="wm-panel d-none" id="wm-panel-why-lineup">
    <header class="wm-panel__head">
        <div>
            <h3 class="wm-panel__title">Why LINEUP</h3>
            <p class="wm-panel__desc">Homepage cards, section heading, and full detail page for each item.</p>
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
            <h4 class="wm-block__title">Cards &amp; detail pages</h4>
            <p class="wm-hint">Each card appears on the homepage. Expand <strong>Full service page</strong> to edit the public detail page for that item.</p>
            @if($editLocale !== 'en')
            <p class="wm-hint wm-hint--info m-b-12"><i class="zmdi zmdi-info-outline"></i> Photos, slugs, and custom links are shared across languages.</p>
            @endif
            <div id="website-features-list">
                @foreach(old('features', $content['features']) as $i => $feature)
                @include('admin.website.partials.feature-card-row', [
                    'i' => $i,
                    'feature' => $feature,
                ])
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-default btn-round m-t-10" id="website-add-feature"><i class="zmdi zmdi-plus"></i> Add card</button>
        </div>
    </div>
</section>
