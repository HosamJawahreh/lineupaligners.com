<section class="wm-panel" id="wm-panel-general">
    <header class="wm-panel__head">
        <div>
            <h3 class="wm-panel__title">Overview</h3>
            <p class="wm-panel__desc">{{ $readiness['percent'] }}% launch ready — pick a section below or adjust site settings.</p>
        </div>
    </header>
    <div class="wm-panel__body">
        <div class="wm-block">
            <h4 class="wm-block__title">Go to section</h4>
            <ul class="wm-jump-list">
                @foreach($contentInventory ?? [] as $item)
                <li>
                    <a href="#wm-panel-{{ $item['section'] }}" class="wm-jump-list__link wm-goto-section @if($item['done']) is-done @endif" data-wm-section="{{ $item['section'] }}">
                        <span class="wm-jump-list__label">{{ $item['label'] }}</span>
                        <span class="wm-jump-list__meta">@if($item['done'])Ready@else{{ $item['hint'] }}@endif</span>
                        <i class="zmdi zmdi-chevron-right"></i>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        <div class="wm-block">
            <h4 class="wm-block__title">Publish</h4>
            <div class="website-publish-toggle">
                <label class="website-switch">
                    <input type="checkbox" name="published" value="1" @checked($content['published'])>
                    <span class="website-switch__slider"></span>
                </label>
                <div>
                    <strong>{{ $content['published'] ? 'Site is live' : 'Site is draft' }}</strong>
                    <p class="wm-hint m-b-0">Draft = admins only. Live = public at <code>/</code>.</p>
                </div>
            </div>
        </div>

        <details class="wm-accordion">
            <summary class="wm-accordion__head">Languages</summary>
            <div class="wm-accordion__body">
                <p class="wm-hint">Languages shown in the site header switcher.</p>
                <div class="website-locale-enable">
                    @foreach($locales as $code => $meta)
                    <label class="website-locale-enable__item">
                        <input type="checkbox" name="enabled_locales[]" value="{{ $code }}" @checked(in_array($code, $enabledLocales ?? ['en', 'ar'], true))>
                        <span class="website-locale-enable__card">
                            <span class="website-locale-enable__flag">{{ $meta['flag'] ?? '' }}</span>
                            <span>
                                <strong>{{ $meta['native'] ?? $code }}</strong>
                                <small class="text-muted d-block">{{ strtoupper($meta['dir'] ?? 'ltr') }}</small>
                            </span>
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
        </details>

        <details class="wm-accordion">
            <summary class="wm-accordion__head">Homepage template</summary>
            <div class="wm-accordion__body">
                <div class="row">
                    @foreach($templates as $key => $template)
                    <div class="col-md-6 m-b-15">
                        <label class="website-template-card @if(empty($template['available'])) is-disabled @endif">
                            <input type="radio" name="website_template" value="{{ $key }}"
                                   @checked(($content['template'] ?? config('website.default_template')) === $key)
                                   @disabled(empty($template['available']))>
                            <span class="website-template-card__body">
                                <strong>{{ $template['label'] }}</strong>
                                @if(!empty($template['coming_soon']))
                                <span class="label label-default m-l-5">Soon</span>
                                @endif
                                <p class="text-muted m-b-0 m-t-5">{{ $template['description'] }}</p>
                            </span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </details>

        <details class="wm-accordion" open>
            <summary class="wm-accordion__head">Homepage sections</summary>
            <div class="wm-accordion__body">
                <p class="wm-hint m-b-12">Turn sections on or off on the public homepage. Use the sidebar to edit each section’s content.</p>
                <div class="wm-section-toggles">
                    @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'about', 'sectionLabel' => 'About', 'variant' => 'grid'])
                    @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'services', 'sectionLabel' => 'Why LINEUP', 'variant' => 'grid'])
                    @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'process', 'sectionLabel' => 'How it works', 'variant' => 'grid'])
                    @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'stats', 'sectionLabel' => 'Stats', 'variant' => 'grid'])
                    @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'partner_cta', 'sectionLabel' => 'Partner CTA', 'variant' => 'grid'])
                    @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'portfolio', 'sectionLabel' => 'Case results', 'variant' => 'grid'])
                    @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'faq', 'sectionLabel' => 'FAQ', 'variant' => 'grid'])
                    @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'blog', 'sectionLabel' => 'Blog', 'variant' => 'grid'])
                    @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'cta_banner', 'sectionLabel' => 'CTA banner', 'variant' => 'grid'])
                </div>
            </div>
        </details>

        <div class="wm-block wm-block--muted">
            <div class="website-branding-card">
                <img src="{{ $logoUrl }}" alt="{{ $projectName }}" width="48" height="48">
                <div>
                    <strong>{{ $projectName }}</strong>
                    <p class="wm-hint m-b-0">Logo & name: <a href="{{ route('settings.index', ['tab' => 'branding']) }}">Settings → Branding</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
