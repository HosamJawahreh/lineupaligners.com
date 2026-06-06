<section class="wm-panel wm-panel--continued d-none" id="wm-panel-case-studies-gallery">
    <div class="wm-panel__body wm-panel__body--flush-top">
        @include('admin.website.partials.showcase-crud', [
            'context' => 'case-studies',
            'formId' => 'showcase-form-case-studies',
            'returnSection' => 'case-studies',
            'showDetailFields' => true,
            'blockTitle' => 'All case studies',
            'blockHint' => 'Each case has a listing card, a detail page, and appears in the homepage carousel when published. Expand Full case study page to edit detail content.',
            'addLabel' => 'Add case study',
            'editLabel' => 'Edit case study',
            'deleteConfirm' => 'Remove this case study?',
            'emptyMessage' => 'No custom cases yet — add your first case using the form.',
        ])
    </div>
</section>
