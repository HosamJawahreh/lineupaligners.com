<section class="wm-panel d-none" id="wm-panel-cta-banner">
    <header class="wm-panel__head">
        <div>
            <h3 class="wm-panel__title">CTA banner</h3>
            <p class="wm-panel__desc">Bottom homepage banner with rating, headline, and button.</p>
        </div>
        @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'cta_banner', 'sectionLabel' => 'CTA banner section'])
    </header>
    <div class="wm-panel__body">
        <div class="wm-block">
            <div class="row">
                <div class="col-md-4">
                    <div class="wm-field">
                        <label class="wm-label">Rating</label>
                        <input type="text" name="cta_rating" class="form-control wm-input" value="{{ old('cta_rating', $content['cta_banner']['rating']) }}" placeholder="98%">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="wm-field">
                        <label class="wm-label">Rating label</label>
                        <input type="text" name="cta_rating_label" class="form-control wm-input" value="{{ old('cta_rating_label', $content['cta_banner']['rating_label']) }}" placeholder="Patient satisfaction">
                    </div>
                </div>
            </div>
            <div class="wm-field">
                <label class="wm-label">Banner label</label>
                <input type="text" name="cta_subtitle" class="form-control wm-input" value="{{ old('cta_subtitle', $content['cta_banner']['subtitle']) }}">
            </div>
            <div class="wm-field">
                <label class="wm-label">Banner title</label>
                <input type="text" name="cta_title" class="form-control wm-input" value="{{ old('cta_title', $content['cta_banner']['title']) }}">
            </div>
            <div class="wm-field">
                <label class="wm-label">Button text</label>
                <input type="text" name="cta_banner_label" class="form-control wm-input" value="{{ old('cta_banner_label', $content['cta_banner']['cta_label']) }}">
            </div>
        </div>
    </div>
</section>
