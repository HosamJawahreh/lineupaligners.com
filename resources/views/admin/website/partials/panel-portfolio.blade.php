<section class="wm-panel d-none" id="wm-panel-portfolio">

    <header class="wm-panel__head">

        <div>

            <h3 class="wm-panel__title">Case results</h3>

            <p class="wm-panel__desc">Homepage before/after carousel — edit the section heading below, then add or manage carousel cases in the panel underneath.</p>

        </div>

        @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'portfolio', 'sectionLabel' => 'Case results'])

    </header>

    <div class="wm-panel__body">

        <div class="wm-block">

            <h4 class="wm-block__title">Homepage section title</h4>

            <input type="text" name="treatments_subtitle" class="form-control wm-input m-b-10" value="{{ old('treatments_subtitle', $content['treatments']['subtitle']) }}" placeholder="Section label">

            <input type="text" name="treatments_title" class="form-control wm-input m-b-10" value="{{ old('treatments_title', $content['treatments']['title']) }}" placeholder="Section title">

            <textarea name="treatments_intro" class="form-control wm-input" rows="2" placeholder="Short introduction shown above the carousel">{{ old('treatments_intro', $content['treatments']['intro']) }}</textarea>

            <p class="wm-hint m-b-0 m-t-10">Save changes to update these headings. Manage carousel items below — they are shared with the Case studies page.</p>

        </div>

    </div>

</section>

