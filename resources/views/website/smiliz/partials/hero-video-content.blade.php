<div class="col-lg-7 col-md-10">
    <div class="lineup-hero-video__content">
        @if(filled($eyebrow ?? ''))
        <p class="lineup-hero-video__eyebrow">{{ $eyebrow }}</p>
        @endif
        @if(filled($title ?? ''))
        <h1 class="lineup-hero-video__title">{!! nl2br(e($title)) !!}</h1>
        @endif
        @if(filled($description ?? ''))
        <p class="lineup-hero-video__desc">{!! nl2br(e($description)) !!}</p>
        @endif
        @if(filled($ctaLabel ?? ''))
        <a class="pbmit-btn lineup-hero-video__cta" href="{{ $ctaUrl ?? $loginUrl }}">
            <span class="pbmit-icon-hover"></span>
            <span class="pbmit-button-content-wrapper">
                <span class="pbmit-button-text">{{ $ctaLabel }}</span>
            </span>
        </a>
        @endif
    </div>
</div>
