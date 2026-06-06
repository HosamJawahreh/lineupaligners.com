<section class="wm-panel d-none" id="wm-panel-about">
    <header class="wm-panel__head">
        <div>
            <h3 class="wm-panel__title">About us</h3>
            <p class="wm-panel__desc">Your story, stats, and photo — shown on the homepage and the About us page.</p>
        </div>
        @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'about', 'sectionLabel' => 'Show on homepage'])
    </header>
    <div class="wm-panel__body">
        @if($editLocale !== 'en')
        <p class="wm-hint wm-hint--info m-b-16"><i class="zmdi zmdi-info-outline"></i> The about photo is shared. Edit text for {{ $locales[$editLocale]['native'] ?? $editLocale }} below.</p>
        @endif

        <div class="wm-block">
            <h4 class="wm-block__title">Section photo</h4>
            @include('admin.website.partials.section-image-field', [
                'label' => 'About image',
                'inputName' => 'about_image',
                'currentUrl' => $websiteContent->aboutImageUrl(),
                'removeName' => filled($content['about']['image'] ?? '') ? 'remove_about_image' : null,
                'previewId' => 'about-image-preview',
                'hint' => 'Large photo on the right side of the about section (Homepage 1) or center image (Homepage 2).',
            ])
        </div>

        <div class="wm-block">
            <h4 class="wm-block__title">Text content</h4>
            <div class="wm-field">
                <label class="wm-label">Section label</label>
                <input type="text" name="about_subtitle" class="form-control wm-input" value="{{ old('about_subtitle', $content['about']['subtitle']) }}" placeholder="e.g. Who We Are">
            </div>
            <div class="wm-field">
                <label class="wm-label">Section title</label>
                <input type="text" name="about_title" class="form-control wm-input" value="{{ old('about_title', $content['about']['title']) }}">
            </div>
            <div class="wm-field">
                <label class="wm-label">Description</label>
                <textarea name="about_body" class="form-control wm-input" rows="5">{{ old('about_body', $content['about']['body']) }}</textarea>
            </div>
            <div class="row">
                <div class="col-sm-4">
                    <div class="wm-field">
                        <label class="wm-label">Years number</label>
                        <input type="number" name="about_years" class="form-control wm-input" min="1" max="100" value="{{ old('about_years', $content['about']['years']) }}">
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="wm-field">
                        <label class="wm-label">Years label</label>
                        <input type="text" name="about_years_label" class="form-control wm-input" value="{{ old('about_years_label', $content['about']['years_label']) }}" placeholder="e.g. Years of aligner expertise">
                    </div>
                </div>
            </div>
            <p class="wm-hint m-b-0">Homepage 2 also shows extra highlight pills below the about block — edit those under <strong>Services</strong> (items 3–6).</p>
        </div>

        <div class="wm-block">
            <h4 class="wm-block__title">Highlight boxes</h4>
            <p class="wm-hint">Two callouts beside the about photo on Homepage 1.</p>
            @php
                $aboutHighlights = old('about_highlights', $content['about']['highlights']);
                while (count($aboutHighlights) < 2) {
                    $aboutHighlights[] = ['title' => '', 'description' => ''];
                }
            @endphp
            @foreach($aboutHighlights as $i => $highlight)
            <div class="wm-faq-item">
                <input type="text" name="about_highlights[{{ $i }}][title]" class="form-control wm-input m-b-8" value="{{ $highlight['title'] ?? '' }}" placeholder="Highlight title">
                <textarea name="about_highlights[{{ $i }}][description]" class="form-control wm-input" rows="2" placeholder="Short description">{{ $highlight['description'] ?? '' }}</textarea>
            </div>
            @endforeach
        </div>

        @php $aboutPage = $content['about_page'] ?? []; @endphp
        <div class="wm-block">
            <h4 class="wm-block__title">About us page (/about)</h4>
            <p class="wm-hint m-b-12">These fields control the public About us page. The bottom banner uses <strong>CTA banner</strong> settings.</p>
            <div class="wm-field">
                <label class="wm-label">Page title (browser &amp; header)</label>
                <input type="text" name="about_page_title" class="form-control wm-input" value="{{ old('about_page_title', $aboutPage['page_title'] ?? 'About Us') }}">
            </div>
            <div class="wm-field">
                <label class="wm-label">Primary button label</label>
                <input type="text" name="about_page_discover_label" class="form-control wm-input" value="{{ old('about_page_discover_label', $aboutPage['discover_label'] ?? 'Doctor Portal') }}">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="wm-field">
                        <label class="wm-label">Team section label</label>
                        <input type="text" name="about_page_team_subtitle" class="form-control wm-input" value="{{ old('about_page_team_subtitle', $aboutPage['team_subtitle'] ?? '') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="wm-field">
                        <label class="wm-label">Team section title</label>
                        <input type="text" name="about_page_team_title" class="form-control wm-input" value="{{ old('about_page_team_title', $aboutPage['team_title'] ?? '') }}">
                    </div>
                </div>
            </div>
            <div class="wm-section-toggles m-t-10">
                <label class="website-switch-row">
                    <input type="hidden" name="about_page_show_team" value="0">
                    <input type="checkbox" name="about_page_show_team" value="1" @checked(old('about_page_show_team', $aboutPage['show_team'] ?? false))>
                    <span>Show team carousel (template demo staff)</span>
                </label>
                <label class="website-switch-row m-t-8">
                    <input type="hidden" name="about_page_show_testimonials" value="0">
                    <input type="checkbox" name="about_page_show_testimonials" value="1" @checked(old('about_page_show_testimonials', $aboutPage['show_testimonials'] ?? false))>
                    <span>Show testimonials block</span>
                </label>
            </div>
        </div>
    </div>
</section>
