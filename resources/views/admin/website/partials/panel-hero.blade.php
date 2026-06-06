<section class="wm-panel d-none" id="wm-panel-hero">
    <header class="wm-panel__head">
        <div>
            <h3 class="wm-panel__title">Homepage</h3>
            <p class="wm-panel__desc">Top banner — headline, background video or slider.</p>
        </div>
    </header>
    <div class="wm-panel__body">
        @if($editLocale !== 'en')
        <p class="wm-hint wm-hint--info m-b-16"><i class="zmdi zmdi-info-outline"></i> Video & photos are shared across languages. Edit text below for {{ $locales[$editLocale]['native'] ?? $editLocale }}.</p>
        @endif

        <div class="wm-field">
            <label class="wm-label">Hero style</label>
            <select name="hero_type" class="form-control wm-input" id="website-hero-type">
                <option value="video" @selected(old('hero_type', $content['hero']['type']) === 'video')>Video background</option>
                <option value="slider" @selected(old('hero_type', $content['hero']['type']) === 'slider')>Image slider</option>
            </select>
        </div>

        <div id="website-hero-video-panel" @class(['wm-block', 'd-none' => old('hero_type', $content['hero']['type']) !== 'video'])>
            <h4 class="wm-block__title">Background video</h4>
            <div class="row align-items-start">
                <div class="col-md-7">
                    <label class="wm-label">Upload video</label>
                    <input type="file" name="hero_video" class="form-control wm-input" accept="video/mp4,video/webm">
                    <p class="wm-hint">MP4 or WebM · max 50MB</p>
                    @if(filled($content['hero']['video'] ?? ''))
                    <label class="wm-check"><input type="checkbox" name="remove_hero_video" value="1"> Reset to default video</label>
                    @endif
                </div>
                <div class="col-md-5">
                    <div class="wm-media-preview wm-media-preview--video">
                        <video src="{{ $heroVideoUrl }}" controls muted playsinline preload="metadata"></video>
                    </div>
                </div>
            </div>
        </div>

        <div id="website-hero-slider-panel" @class(['wm-block', 'd-none' => old('hero_type', $content['hero']['type']) === 'video'])>
            <h4 class="wm-block__title">Slider slides</h4>
            <p class="wm-hint">Each slide has its own photo and text.</p>
            <div class="website-repeatable website-repeatable--slides m-b-15" id="website-slides-list">
                @foreach(old('slides', $content['slides']) as $i => $slide)
                <div class="website-slide-row wm-slide-card wm-slide-card--full">
                    <div class="wm-slide-card__head">
                        <strong>Slide {{ $i + 1 }}</strong>
                    </div>
                    <input type="hidden" name="slides[{{ $i }}][image]" value="{{ $slide['image'] ?? '' }}">
                    <div class="row">
                        <div class="col-md-4">
                            @include('admin.website.partials.section-image-field', [
                                'label' => 'Background photo',
                                'inputName' => "slides[{$i}][image_file]",
                                'currentUrl' => $websiteContent->slideImageUrl($slide),
                                'removeName' => !empty($slide['image']) ? "slides[{$i}][remove_image]" : null,
                                'previewId' => 'slide-preview-'.$i,
                                'compact' => true,
                            ])
                        </div>
                        <div class="col-md-8">
                            <div class="wm-field">
                                <label class="wm-label">Small label</label>
                                <input type="text" name="slides[{{ $i }}][eyebrow]" class="form-control wm-input" value="{{ $slide['eyebrow'] ?? '' }}">
                            </div>
                            <div class="wm-field">
                                <label class="wm-label">Headline</label>
                                <input type="text" name="slides[{{ $i }}][title]" class="form-control wm-input" value="{{ $slide['title'] ?? '' }}">
                            </div>
                            <div class="wm-field">
                                <label class="wm-label">Button text</label>
                                <input type="text" name="slides[{{ $i }}][cta_label]" class="form-control wm-input" value="{{ $slide['cta_label'] ?? 'Doctor Portal' }}">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-simple btn-danger website-remove-row"><i class="zmdi zmdi-delete"></i> Remove slide</button>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-default btn-round" id="website-add-slide"><i class="zmdi zmdi-plus"></i> Add slide</button>
        </div>

        <div class="wm-block">
            <h4 class="wm-block__title">Hero text</h4>
            <p class="wm-hint">Used on the video hero (and as defaults for new slider slides).</p>
            <div class="wm-field">
                <label class="wm-label">Small label</label>
                <input type="text" name="hero_eyebrow" class="form-control wm-input" value="{{ old('hero_eyebrow', $content['hero']['eyebrow']) }}">
            </div>
            <div class="wm-field">
                <label class="wm-label">Main headline</label>
                <input type="text" name="hero_title" class="form-control wm-input" value="{{ old('hero_title', $content['hero']['title']) }}" @if($editLocale === 'en') required @endif>
            </div>
            <div class="wm-field">
                <label class="wm-label">Subheadline</label>
                <textarea name="hero_subtitle" class="form-control wm-input" rows="3" @if($editLocale === 'en') required @endif>{{ old('hero_subtitle', $content['hero']['subtitle']) }}</textarea>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="wm-field">
                        <label class="wm-label">Button text</label>
                        <input type="text" name="hero_cta_label" class="form-control wm-input" value="{{ old('hero_cta_label', $content['hero']['cta_label']) }}">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="wm-field">
                        <label class="wm-label">Button link</label>
                        <input type="text" name="hero_cta_url" class="form-control wm-input" value="{{ old('hero_cta_url', $content['hero']['cta_url']) }}" placeholder="/login">
                        @if($editLocale !== 'en')
                        <p class="wm-hint m-b-0 m-t-5"><i class="zmdi zmdi-info-outline"></i> Link is shared across languages.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="wm-block">
            <h4 class="wm-block__title">Side image <small class="text-muted">(optional)</small></h4>
            @include('admin.website.partials.section-image-field', [
                'label' => 'Photo beside hero text',
                'inputName' => 'hero_image',
                'currentUrl' => $heroImageUrl,
                'removeName' => $heroImageUrl ? 'remove_hero_image' : null,
                'previewId' => 'hero-side-preview',
            ])
        </div>
    </div>
</section>
