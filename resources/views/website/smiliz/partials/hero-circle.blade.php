<div class="col-md-6">
    <div class="pbmit-banner-circle">
        <div class="pbmit-slider-content">
            @if(filled($eyebrow ?? ''))
            <h5 class="pbmit-slider-subtitle transform-top transform-delay-1">{{ $eyebrow }}</h5>
            @endif
            <h2 class="pbmit-slider-title transform-left transform-delay-2">{!! nl2br(e($title ?? '')) !!}</h2>
            @if(filled($description ?? ''))
            <p class="pbmit-slider-desc transform-bottom transform-delay-3">{!! nl2br(e($description)) !!}</p>
            @endif
        </div>
        @if(filled($ctaLabel ?? ''))
        <a class="pbmit-slider-btn transform-center transform-delay-4" href="{{ $ctaUrl ?? $loginUrl }}">
            {!! nl2br(e($ctaLabel)) !!}
            <i class="pbmit-base-icon-up-right-arrow"></i>
        </a>
        @endif
    </div>
</div>
