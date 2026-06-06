<section class="wm-panel d-none" id="wm-panel-faq">
    <header class="wm-panel__head">
        <div>
            <h3 class="wm-panel__title">FAQ</h3>
            <p class="wm-panel__desc">Questions and answers shown near the bottom of the homepage.</p>
        </div>
        @include('admin.website.partials.section-visibility-toggle', ['sectionKey' => 'faq', 'sectionLabel' => 'Show on homepage'])
    </header>
    <div class="wm-panel__body">
        <div class="wm-block">
            <h4 class="wm-block__title">Homepage section title</h4>
            <div class="row m-b-10">
                <div class="col-md-4">
                    <input type="text" name="faq_subtitle" class="form-control wm-input" value="{{ old('faq_subtitle', $content['faq']['subtitle']) }}" placeholder="FAQ label">
                </div>
                <div class="col-md-8">
                    <input type="text" name="faq_title" class="form-control wm-input" value="{{ old('faq_title', $content['faq']['title']) }}" placeholder="FAQ title">
                </div>
            </div>
        </div>

        <div class="wm-block">
            <h4 class="wm-block__title">Questions</h4>
            <div class="website-repeatable" id="website-faq-list">
                @foreach(old('faq_items', $content['faq']['items']) as $i => $item)
                <div class="wm-faq-item">
                    <input type="text" name="faq_items[{{ $i }}][question]" class="form-control wm-input m-b-8" value="{{ $item['question'] ?? '' }}" placeholder="Question">
                    <textarea name="faq_items[{{ $i }}][answer]" class="form-control wm-input" rows="2" placeholder="Answer">{{ $item['answer'] ?? '' }}</textarea>
                    <button type="button" class="btn btn-sm btn-simple btn-danger website-remove-row m-t-8"><i class="zmdi zmdi-delete"></i> Remove</button>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-default btn-round m-t-10" id="website-add-faq"><i class="zmdi zmdi-plus"></i> Add question</button>
        </div>
    </div>
</section>
