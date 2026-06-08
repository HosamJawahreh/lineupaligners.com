@php
    $nav = $content['navigation'] ?? [];
    $structuralLocked = ($editLocale ?? 'en') !== 'en';
    $column = $nav['footer_columns'][0] ?? ['title' => 'Our company', 'links' => []];
    $services = $nav['services_column'] ?? [];
    $newsletter = $nav['newsletter'] ?? [];
    $utility = $nav['footer_utility'] ?? [];
    $bottomLinks = $nav['bottom_links'] ?? [];
    $socialLinks = $nav['social_links'] ?? [];
@endphp
<section class="wm-panel d-none" id="wm-panel-navigation">
    <header class="wm-panel__head">
        <div>
            <h3 class="wm-panel__title">Footer</h3>
            <p class="wm-panel__desc">Menus, social links, and labels at the bottom of every page.</p>
        </div>
    </header>
    <div class="wm-panel__body">
        @if($structuralLocked)
        <p class="wm-hint wm-hint--info m-b-16">
            <i class="zmdi zmdi-info-outline"></i>
            Translating footer text. URLs & link targets — edit in English.
        </p>
        @endif

        <div class="wm-block m-b-20">
            <h4 class="wm-block__title">Footer logo</h4>
            <p class="wm-hint m-b-12">Image shown in the first footer column. Leave empty to use your dashboard logo.</p>
            @if($structuralLocked)
            <div class="wm-section-media wm-section-media--logo">
                <div class="wm-section-media__preview">
                    <img src="{{ $websiteContent->footerImageUrl() }}" alt="">
                </div>
                <div class="wm-section-media__controls">
                    <p class="wm-hint m-b-0">Upload or remove the footer image in English.</p>
                </div>
            </div>
            @else
            @include('admin.website.partials.section-image-field', [
                'label' => 'Footer image',
                'inputName' => 'footer_image',
                'currentUrl' => $websiteContent->footerImageUrl(),
                'removeName' => filled($nav['footer_image'] ?? '') ? 'remove_footer_image' : null,
                'previewId' => 'footer-image-preview',
                'logoField' => true,
                'hint' => 'PNG or SVG-friendly logo on a transparent background works best.',
            ])
            @endif
        </div>

        <details class="wm-accordion" open>
            <summary class="wm-accordion__head">Social media</summary>
            <div class="wm-accordion__body">
                <p class="wm-hint">Leave blank to hide an icon.</p>
                <div class="wm-social-grid">
                    @foreach($socialLinks as $i => $social)
                    <div class="wm-social-grid__item">
                        <input type="hidden" name="navigation[social_links][{{ $i }}][network]" value="{{ $social['network'] }}">
                        <input type="hidden" name="navigation[social_links][{{ $i }}][title]" value="{{ $social['title'] }}">
                        <label class="wm-label">{{ ucfirst($social['network']) }}</label>
                        <input type="url" name="navigation[social_links][{{ $i }}][url]" class="form-control wm-input"
                               value="{{ old('navigation.social_links.'.$i.'.url', $social['url'] ?? '') }}"
                               placeholder="https://…" @disabled($structuralLocked)>
                    </div>
                    @endforeach
                </div>
            </div>
        </details>

        <details class="wm-accordion" open>
            <summary class="wm-accordion__head">Footer menu</summary>
            <div class="wm-accordion__body">
                <div class="wm-field">
                    <label class="wm-label">Column title</label>
                    <input type="text" name="navigation[footer_columns][0][title]" class="form-control wm-input"
                           value="{{ old('navigation.footer_columns.0.title', $column['title'] ?? '') }}">
                </div>
                <div class="wm-nav-links" id="wm-footer-links-list">
                    @foreach($column['links'] ?? [] as $li => $link)
                    @include('admin.website.partials.navigation-link-row', [
                        'prefix' => 'navigation[footer_columns][0][links]['.$li.']',
                        'link' => $link,
                        'structuralLocked' => $structuralLocked,
                    ])
                    @endforeach
                </div>
                @unless($structuralLocked)
                <button type="button" class="btn btn-sm btn-default btn-round m-t-10" id="wm-add-footer-link">
                    <i class="zmdi zmdi-plus"></i> Add link
                </button>
                @endunless
            </div>
        </details>

        <details class="wm-accordion">
            <summary class="wm-accordion__head">Services & newsletter columns</summary>
            <div class="wm-accordion__body">
                <div class="wm-subsection">
                    <h5 class="wm-subsection__title">Services column</h5>
                    <label class="wm-inline-check">
                        <input type="checkbox" name="navigation[services_column][enabled]" value="1"
                               @checked(old('navigation.services_column.enabled', $services['enabled'] ?? true)) @disabled($structuralLocked)>
                        Show in footer
                    </label>
                    <input type="text" name="navigation[services_column][title]" class="form-control wm-input m-t-10"
                           value="{{ old('navigation.services_column.title', $services['title'] ?? '') }}" placeholder="Column title">
                    <label class="wm-inline-check m-t-10">
                        <input type="checkbox" name="navigation[services_column][use_features]" value="1"
                               @checked(old('navigation.services_column.use_features', $services['use_features'] ?? true)) @disabled($structuralLocked)>
                        Auto-list from Features section
                    </label>
                    @unless($structuralLocked)
                    <div class="row m-t-10">
                        <div class="col-xs-4">
                            <input type="number" min="1" max="10" name="navigation[services_column][feature_limit]"
                                   class="form-control wm-input" value="{{ old('navigation.services_column.feature_limit', $services['feature_limit'] ?? 5) }}" title="How many items">
                        </div>
                        <div class="col-xs-8">
                            <input type="text" name="navigation[services_column][feature_link]" class="form-control wm-input"
                                   value="{{ old('navigation.services_column.feature_link', $services['feature_link'] ?? '#services') }}" placeholder="Link e.g. #services">
                        </div>
                    </div>
                    @endunless
                </div>
                <div class="wm-subsection m-t-20">
                    <h5 class="wm-subsection__title">Newsletter</h5>
                    <label class="wm-inline-check">
                        <input type="checkbox" name="navigation[newsletter][enabled]" value="1"
                               @checked(old('navigation.newsletter.enabled', $newsletter['enabled'] ?? true)) @disabled($structuralLocked)>
                        Show signup form
                    </label>
                    <input type="text" name="navigation[newsletter][title]" class="form-control wm-input m-t-10"
                           value="{{ old('navigation.newsletter.title', $newsletter['title'] ?? '') }}" placeholder="Title (optional)">
                    <textarea name="navigation[newsletter][blurb]" class="form-control wm-input m-t-10" rows="2"
                              placeholder="Description — use {project} for clinic name">{{ old('navigation.newsletter.blurb', $newsletter['blurb'] ?? '') }}</textarea>
                </div>
            </div>
        </details>

        <details class="wm-accordion">
            <summary class="wm-accordion__head">Contact bar labels</summary>
            <div class="wm-accordion__body">
                <p class="wm-hint">Phone, email &amp; address values come from Contact.</p>
                @foreach($utility as $ui => $row)
                <div class="wm-utility-row">
                    <input type="hidden" name="navigation[footer_utility][{{ $ui }}][source]" value="{{ $row['source'] }}">
                    <input type="text" name="navigation[footer_utility][{{ $ui }}][label]" class="form-control wm-input"
                           value="{{ old('navigation.footer_utility.'.$ui.'.label', $row['label'] ?? '') }}"
                           placeholder="{{ ucfirst($row['source']) }} label">
                    @if(($row['source'] ?? '') === 'chat')
                    <input type="text" name="navigation[footer_utility][{{ $ui }}][chat_label]" class="form-control wm-input"
                           value="{{ old('navigation.footer_utility.'.$ui.'.chat_label', $row['chat_label'] ?? 'Chat with us') }}"
                           placeholder="Chat link text">
                    @endif
                </div>
                @endforeach
            </div>
        </details>

        <details class="wm-accordion">
            <summary class="wm-accordion__head">Copyright bar links</summary>
            <div class="wm-accordion__body">
                <div class="wm-nav-links" id="wm-bottom-links-list">
                    @foreach($bottomLinks as $bi => $link)
                    @include('admin.website.partials.navigation-link-row', [
                        'prefix' => 'navigation[bottom_links]['.$bi.']',
                        'link' => $link,
                        'structuralLocked' => $structuralLocked,
                    ])
                    @endforeach
                </div>
                @unless($structuralLocked)
                <button type="button" class="btn btn-sm btn-default btn-round m-t-10" id="wm-add-bottom-link">
                    <i class="zmdi zmdi-plus"></i> Add link
                </button>
                @endunless
            </div>
        </details>
    </div>
</section>
