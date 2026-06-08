@extends('layouts.website-manage')

@section('title', 'Manage Website')
@section('website-heading', 'Website')
@section('website-subheading', 'Edit your public marketing site')

@section('website-content')
@include('admin.website.partials.toolbar')

<div class="wm-workspace">
    @include('admin.website.partials.sidebar')

    <div class="wm-main" id="wm-main-form">
        <form method="POST" action="{{ route('admin.website.content.update') }}" id="website-content-form" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_tab" id="website-return-tab" value="general">
            <input type="hidden" name="edit_locale" value="{{ $editLocale }}">

            @include('admin.website.partials.panel-general')
            @include('admin.website.partials.panel-hero')
            @include('admin.website.partials.panel-why-lineup')
            @include('admin.website.partials.panel-how-it-works')
            @include('admin.website.partials.panel-about')
            @include('admin.website.partials.panel-stats')
            @include('admin.website.partials.panel-portfolio')
            @include('admin.website.partials.panel-faq')
            @include('admin.website.partials.panel-blog')
            @include('admin.website.partials.panel-partner')
            @include('admin.website.partials.panel-cta-banner')
            @include('admin.website.partials.panel-contact')
            @include('admin.website.partials.panel-navigation')
            @include('admin.website.partials.panel-case-studies')

            <div class="wm-savebar" id="website-save-actions">
                <p class="wm-savebar__hint">Saving updates the <strong>{{ $locales[$editLocale]['native'] ?? $editLocale }}</strong> version of your site.</p>
                <button type="submit" class="btn btn-primary btn-round">
                    <i class="zmdi zmdi-check m-r-5"></i> Save changes
                </button>
            </div>
        </form>

        @include('admin.website.partials.panel-portfolio-gallery')
        @include('admin.website.partials.panel-case-studies-gallery')
    </div>

    <div class="wm-main wm-main--solo d-none" id="wm-main-solo">
        @include('admin.website.partials.panel-main-menu')
    </div>
</div>

@include('admin.website.partials.templates')
@endsection

@push('website-scripts')
<script>
window.websiteAdminConfig = {
    featureIndex: {{ count(old('features', $content['features'])) }},
    statIndex: {{ count(old('stats', $content['stats'])) }},
    slideIndex: {{ count(old('slides', $content['slides'])) }},
    processIndex: {{ count(old('process_steps', $content['process']['steps'])) }},
    faqIndex: {{ count(old('faq_items', $content['faq']['items'])) }},
    blogIndex: {{ count(old('blog_posts', $content['blog']['items'])) }},
    navLinkIndex: {{ count(old('navigation.footer_columns.0.links', $content['navigation']['footer_columns'][0]['links'] ?? [])) }},
    bottomLinkIndex: {{ count(old('navigation.bottom_links', $content['navigation']['bottom_links'] ?? [])) }},
    showcaseStoreUrl: @json(route('admin.website.showcases.store')),
    pageLinkOptionsHtml: @json(collect($pageLinkOptions ?? [])->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values()),
};
</script>
@endpush
