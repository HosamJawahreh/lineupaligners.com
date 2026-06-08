@php
    $casePhotosBySet = $casePhotosBySet ?? [];
    $defaultScanSetKey = $defaultScanSetKey ?? 'original';
    $hasAnyPhotos = collect($casePhotosBySet)->flatten(1)->isNotEmpty();
    $initialPhotos = $casePhotosBySet[$defaultScanSetKey] ?? [];
@endphp
@if($hasAnyPhotos)
<button type="button"
        class="case-photos-gallery-trigger"
        id="case-photos-gallery-open"
        aria-haspopup="dialog"
        aria-controls="case-photos-gallery-modal"
        title="Open case photos gallery"
        @if(count($initialPhotos) === 0) hidden @endif>
    <span class="case-photos-gallery-trigger__glow" aria-hidden="true"></span>
    <span class="case-photos-gallery-trigger__inner">
        <span class="case-photos-gallery-trigger__thumbs" id="case-photos-gallery-trigger-thumbs" aria-hidden="true">
            @foreach(collect($initialPhotos)->take(3) as $photo)
            <img src="{{ $photo['url'] }}" alt="">
            @endforeach
            @if(count($initialPhotos) > 3)
            <span class="case-photos-gallery-trigger__more" id="case-photos-gallery-trigger-more">+{{ count($initialPhotos) - 3 }}</span>
            @endif
        </span>
        <span class="case-photos-gallery-trigger__text">
            <span class="case-photos-gallery-trigger__label">Case Photos Gallery</span>
            <span class="case-photos-gallery-trigger__count" id="case-photos-gallery-trigger-count">{{ count($initialPhotos) }} {{ Str::plural('photo', count($initialPhotos)) }}</span>
        </span>
        <i class="zmdi zmdi-fullscreen case-photos-gallery-trigger__icon" aria-hidden="true"></i>
    </span>
</button>

@once
@push('case-photos-gallery')
<div class="case-photos-gallery-modal"
     id="case-photos-gallery-modal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="case-photos-gallery-title"
     hidden>
    <div class="case-photos-gallery-modal__backdrop" data-case-photos-close></div>
    <div class="case-photos-gallery-modal__panel">
        <header class="case-photos-gallery-modal__header">
            <div class="case-photos-gallery-modal__brand">
                <span class="case-photos-gallery-modal__brand-icon" aria-hidden="true">
                    <i class="zmdi zmdi-collection-image"></i>
                </span>
                <div class="case-photos-gallery-modal__brand-text">
                    <h2 id="case-photos-gallery-title">Case Photos</h2>
                    <div class="case-photos-gallery-modal__chips">
                        <span class="case-photos-gallery-chip" id="case-photos-gallery-scope"></span>
                        <span class="case-photos-gallery-chip case-photos-gallery-chip--count" id="case-photos-gallery-counter">0 / 0</span>
                    </div>
                </div>
            </div>
            <div class="case-photos-gallery-modal__toolbar">
                <a href="#"
                   class="case-photos-gallery-tool"
                   id="case-photos-download-all"
                   download>
                    <i class="zmdi zmdi-cloud-download" aria-hidden="true"></i>
                    <span>Download all</span>
                </a>
                <button type="button" class="case-photos-gallery-tool case-photos-gallery-tool--icon" data-case-photos-close aria-label="Close gallery">
                    <i class="zmdi zmdi-close" aria-hidden="true"></i>
                </button>
            </div>
        </header>

        <div class="case-photos-gallery-modal__progress" aria-hidden="true">
            <div class="case-photos-gallery-modal__progress-track">
                <div class="case-photos-gallery-modal__progress-fill" id="case-photos-gallery-progress"></div>
            </div>
        </div>

        <div class="case-photos-gallery-modal__body">
            <div class="case-photos-gallery-modal__stage">
                <div class="case-photos-gallery-modal__ambient" aria-hidden="true">
                    <span class="case-photos-gallery-modal__orb case-photos-gallery-modal__orb--a"></span>
                    <span class="case-photos-gallery-modal__orb case-photos-gallery-modal__orb--b"></span>
                </div>
                <button type="button" class="case-photos-gallery-nav case-photos-gallery-nav--prev" id="case-photos-prev" aria-label="Previous photo">
                    <i class="zmdi zmdi-chevron-left" aria-hidden="true"></i>
                </button>
                <figure class="case-photos-gallery-modal__viewport">
                    <div class="case-photos-gallery-modal__frame">
                        <img src="" alt="" id="case-photos-gallery-image" class="case-photos-gallery-modal__image">
                        <p class="case-photos-gallery-modal__empty is-hidden" id="case-photos-gallery-empty">
                            <i class="zmdi zmdi-image-alt"></i>
                            No photos in this scan version
                        </p>
                    </div>
                    <figcaption class="case-photos-gallery-modal__caption" id="case-photos-gallery-filename"></figcaption>
                </figure>
                <button type="button" class="case-photos-gallery-nav case-photos-gallery-nav--next" id="case-photos-next" aria-label="Next photo">
                    <i class="zmdi zmdi-chevron-right" aria-hidden="true"></i>
                </button>
            </div>

            <aside class="case-photos-gallery-modal__rail">
                <div class="case-photos-gallery-modal__rail-head">
                    <span class="case-photos-gallery-modal__rail-label">Gallery</span>
                </div>
                <div class="case-photos-gallery-thumbs" id="case-photos-gallery-thumbs" role="tablist" aria-label="Photo thumbnails"></div>
                <a href="#"
                   class="case-photos-gallery-download"
                   id="case-photos-download-current"
                   download>
                    <i class="zmdi zmdi-download" aria-hidden="true"></i>
                    <span>Download this photo</span>
                </a>
                <p class="case-photos-gallery-modal__keys">
                    <kbd>←</kbd><kbd>→</kbd> navigate
                    <span class="case-photos-gallery-modal__keys-sep">·</span>
                    <kbd>Esc</kbd> close
                </p>
            </aside>
        </div>
    </div>
</div>
<script>
window.casePhotosGalleryBySet = @json($casePhotosBySet);
window.casePhotosGallerySetLabels = @json(collect($caseScanSets ?? [])->pluck('label', 'key'));
window.casePhotosDownloadAllBaseUrl = @json(route('patients.photos.download-all', $patient));
window.casePhotosGalleryDefaultSet = @json($defaultScanSetKey);
</script>
@endpush
@endonce
@endif
