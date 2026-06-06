<section class="wm-panel wm-panel--continued d-none" id="wm-panel-portfolio-gallery">
    <div class="wm-panel__body wm-panel__body--flush-top">
        @include('admin.website.partials.showcase-crud', [
            'context' => 'portfolio',
            'formId' => 'showcase-form-portfolio',
            'returnSection' => 'portfolio',
            'showDetailFields' => false,
            'blockTitle' => 'Homepage carousel cases',
            'blockHint' => 'Add, edit, or delete before/after cases shown in the homepage Case results carousel. Published cases also appear on the Case studies listing page.',
            'addLabel' => 'Add case result',
            'editLabel' => 'Edit case result',
            'deleteConfirm' => 'Remove this case from the homepage carousel?',
            'emptyMessage' => 'No case results yet — add your first before/after pair using the form.',
        ])
    </div>
</section>
