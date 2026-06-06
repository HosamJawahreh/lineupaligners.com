<section class="wm-panel d-none" id="wm-panel-how-it-works">
    <header class="wm-panel__head">
        <div>
            <h3 class="wm-panel__title">How it works</h3>
            <p class="wm-panel__desc">Homepage workflow section — heading and step-by-step cards with dashboard screenshots.</p>
        </div>
        @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'process', 'sectionLabel' => 'How it works section'])
    </header>
    <div class="wm-panel__body">
        <div class="wm-block">
            <h4 class="wm-block__title">Section header</h4>
            <div class="row m-b-10">
                <div class="col-md-6">
                    <input type="text" name="process_subtitle" class="form-control wm-input" value="{{ old('process_subtitle', $content['process']['subtitle']) }}" placeholder="Section label">
                </div>
                <div class="col-md-6">
                    <input type="text" name="process_title" class="form-control wm-input" value="{{ old('process_title', $content['process']['title']) }}" placeholder="Section title">
                </div>
            </div>
        </div>

        <div class="wm-block">
            <h4 class="wm-block__title">Steps</h4>
            @if($editLocale !== 'en')
            <p class="wm-hint wm-hint--info m-b-12"><i class="zmdi zmdi-info-outline"></i> Dashboard screenshots are shared across languages.</p>
            @endif
            <div id="website-process-list">
                @foreach(old('process_steps', $content['process']['steps']) as $i => $step)
                <div class="wm-process-card website-repeatable__row">
                    <input type="hidden" name="process_steps[{{ $i }}][image]" value="{{ $step['image'] ?? '' }}">
                    @include('admin.website.partials.section-image-field', [
                        'inputName' => "process_steps[{$i}][image_file]",
                        'currentUrl' => $websiteContent->processStepImageUrl($step, $i),
                        'removeName' => !empty($step['image']) ? "process_steps[{$i}][remove_image]" : null,
                        'previewId' => 'process-preview-'.$i,
                        'compact' => true,
                        'label' => 'Dashboard screenshot',
                    ])
                    <div class="wm-process-card__fields">
                        <input type="text" name="process_steps[{{ $i }}][title]" class="form-control wm-input" value="{{ $step['title'] ?? '' }}" placeholder="Step title">
                        <textarea name="process_steps[{{ $i }}][description]" class="form-control wm-input" rows="2" placeholder="Description">{{ $step['description'] ?? '' }}</textarea>
                    </div>
                    <button type="button" class="btn btn-sm btn-simple btn-danger website-remove-row" title="Remove"><i class="zmdi zmdi-close"></i></button>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-default btn-round m-t-10" id="website-add-process"><i class="zmdi zmdi-plus"></i> Add step</button>
        </div>
    </div>
</section>
