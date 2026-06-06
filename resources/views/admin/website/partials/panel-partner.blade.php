<section class="wm-panel d-none" id="wm-panel-partner">
    <header class="wm-panel__head">
        <div>
            <h3 class="wm-panel__title">Partner CTA</h3>
            <p class="wm-panel__desc">Doctor partnership panel — mainly used on Homepage 2 layout.</p>
        </div>
        @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'partner_cta', 'sectionLabel' => 'Partner CTA section'])
    </header>
    <div class="wm-panel__body">
        <div class="wm-block">
            <input type="text" name="partner_quote" class="form-control wm-input m-b-10" value="{{ old('partner_quote', $content['partner_cta']['quote']) }}" placeholder="Quote">
            <input type="text" name="partner_title" class="form-control wm-input m-b-10" value="{{ old('partner_title', $content['partner_cta']['title']) }}" placeholder="Title">
            <textarea name="partner_body" class="form-control wm-input m-b-10" rows="3" placeholder="Body">{{ old('partner_body', $content['partner_cta']['body']) }}</textarea>
            <input type="text" name="partner_cta_label" class="form-control wm-input" value="{{ old('partner_cta_label', $content['partner_cta']['cta_label']) }}" placeholder="Button text">
        </div>
    </div>
</section>
