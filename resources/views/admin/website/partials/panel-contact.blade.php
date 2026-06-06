<section class="wm-panel d-none" id="wm-panel-contact">
    <header class="wm-panel__head">
        <div>
            <h3 class="wm-panel__title">Contact</h3>
            <p class="wm-panel__desc">Phone, email, address, and contact page content — plus search engine settings.</p>
        </div>
    </header>
    <div class="wm-panel__body">
        <div class="wm-block">
            <h4 class="wm-block__title">Contact details</h4>
            <p class="wm-hint wm-hint--info m-b-12"><i class="zmdi zmdi-info-outline"></i> Phone, email, and address are shared across languages.</p>
            <div class="wm-field">
                <label class="wm-label">Footer tagline</label>
                <input type="text" name="footer_tagline" class="form-control wm-input" value="{{ old('footer_tagline', $content['contact']['tagline']) }}">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="wm-field">
                        <label class="wm-label">Email</label>
                        <input type="email" name="contact_email" class="form-control wm-input" value="{{ old('contact_email', $content['contact']['email']) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="wm-field">
                        <label class="wm-label">Phone</label>
                        <input type="text" name="contact_phone" class="form-control wm-input" value="{{ old('contact_phone', $content['contact']['phone']) }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="wm-field">
                        <label class="wm-label">Hours</label>
                        <input type="text" name="contact_hours" class="form-control wm-input" value="{{ old('contact_hours', $content['contact']['hours']) }}">
                        <p class="wm-hint m-b-0">Shown on the contact page under the phone number.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="wm-field">
                        <label class="wm-label">Address</label>
                        <input type="text" name="contact_address" class="form-control wm-input" value="{{ old('contact_address', $content['contact']['address']) }}" placeholder="Amman, Jordan">
                        <p class="wm-hint m-b-0">Used in the header, footer, contact page, and map embed.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="wm-block">
            <h4 class="wm-block__title">Contact page</h4>
            @php $contactPage = $content['contact']['page'] ?? []; @endphp
            <div class="row">
                <div class="col-md-6">
                    <div class="wm-field">
                        <label class="wm-label">Page eyebrow</label>
                        <input type="text" name="contact_page_subtitle" class="form-control wm-input" value="{{ old('contact_page_subtitle', $contactPage['subtitle'] ?? '') }}" placeholder="Contact us">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="wm-field">
                        <label class="wm-label">Page title</label>
                        <input type="text" name="contact_page_title" class="form-control wm-input" value="{{ old('contact_page_title', $contactPage['title'] ?? '') }}" placeholder="Reach out for best treatment">
                    </div>
                </div>
            </div>
            <div class="wm-field">
                <label class="wm-label">Page intro</label>
                <textarea name="contact_page_intro" class="form-control wm-input" rows="2" placeholder="Have questions or ready to contact us today...">{{ old('contact_page_intro', $contactPage['intro'] ?? '') }}</textarea>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="wm-field">
                        <label class="wm-label">Email card title</label>
                        <input type="text" name="contact_page_email_title" class="form-control wm-input" value="{{ old('contact_page_email_title', $contactPage['email_title'] ?? '') }}" placeholder="Mail us 24/7">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="wm-field">
                        <label class="wm-label">Phone card title</label>
                        <input type="text" name="contact_page_phone_title" class="form-control wm-input" value="{{ old('contact_page_phone_title', $contactPage['phone_title'] ?? '') }}" placeholder="Call us 24/7">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="wm-field">
                        <label class="wm-label">Location card title</label>
                        <input type="text" name="contact_page_location_title" class="form-control wm-input" value="{{ old('contact_page_location_title', $contactPage['location_title'] ?? '') }}" placeholder="Our location">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="wm-field">
                        <label class="wm-label">Form title <span class="text-muted">(Contact page variant 2)</span></label>
                        <input type="text" name="contact_page_form_title" class="form-control wm-input" value="{{ old('contact_page_form_title', $contactPage['form_title'] ?? '') }}" placeholder="Book an appointment">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="wm-field">
                        <label class="wm-label">Form intro <span class="text-muted">(Contact page variant 2)</span></label>
                        <input type="text" name="contact_page_form_intro" class="form-control wm-input" value="{{ old('contact_page_form_intro', $contactPage['form_intro'] ?? '') }}" placeholder="Call us to schedule an appointment">
                    </div>
                </div>
            </div>
        </div>

        <div class="wm-block">
            <h4 class="wm-block__title">Page header background</h4>
            <p class="wm-hint m-b-12">Background image behind the page title and breadcrumb on About, Services, Blog, FAQ, Contact, and other inner pages.</p>
            @include('admin.website.partials.section-image-field', [
                'label' => 'Breadcrumb banner',
                'inputName' => 'titlebar_image',
                'currentUrl' => $websiteContent->titleBarImageUrl(),
                'removeName' => filled($content['seo']['titlebar_image'] ?? '') ? 'remove_titlebar_image' : null,
                'previewId' => 'titlebar-image-preview',
                'hint' => 'Wide landscape photo works best (about 1920×450px).',
            ])
        </div>

        <div class="wm-block">
            <h4 class="wm-block__title">SEO</h4>
            <div class="wm-field">
                <label class="wm-label">Browser tab title</label>
                <input type="text" name="meta_title" class="form-control wm-input" value="{{ old('meta_title', $content['seo']['meta_title']) }}">
            </div>
            <div class="wm-field">
                <label class="wm-label">Meta description</label>
                <textarea name="meta_description" class="form-control wm-input" rows="2" maxlength="320">{{ old('meta_description', $content['seo']['meta_description']) }}</textarea>
            </div>
        </div>
    </div>
</section>
