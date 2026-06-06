@if($content['sections']['practice_care'] ?? true)

@php
    $careItems = collect($content['practice_care']['items'] ?? [])->filter(fn ($item) => filled($item['title'] ?? null));
    $careCta = $content['practice_care']['cta'] ?? [];
@endphp

@if($careItems->isNotEmpty())
<section class="pbmit-bg-color-light lineup-practice-care" id="practice-care">
    <section class="pbmit-bg-color-secondary transform-top-sec bottom-radius service-section-two">
        <div class="container">
            <div class="pbmit-element-service-style-2" data-aos="fade-in" data-aos-duration="1250">
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        <div class="pbmit-heading-subheading pt-4 mt-3">
                            <h4 class="pbmit-subtitle">{{ $content['practice_care']['subtitle'] }}</h4>
                            <h2 class="pbmit-title text-white">{!! nl2br(e($content['practice_care']['title'])) !!}</h2>
                        </div>
                    </div>
                    @foreach($careItems as $item)
                    @php
                        $itemUrl = $websiteContent->practiceCareItemUrl($item, $loginUrl);
                    @endphp
                    <div class="col-md-6 col-lg-4 pbmit-ele pbmit-box-area">
                        <div class="pbminfotech-post-item">
                            <div class="pbmit-box-content-wrap">
                                @if(filled($item['smiliz_icon'] ?? null))
                                <div class="pbmit-service-icon">
                                    <i class="pbmit-smiliz-icon {{ $item['smiliz_icon'] }}" aria-hidden="true"></i>
                                </div>
                                @endif
                                <div class="pbmit-content-box-wrap">
                                    <h3 class="pbmit-service-title">
                                        <a href="{{ $itemUrl }}">{{ $item['title'] }}</a>
                                    </h3>
                                </div>
                            </div>
                            <div class="pbmit-content-box">
                                @if(filled($item['description'] ?? null))
                                <div class="pbmit-service-description"><p>{{ $item['description'] }}</p></div>
                                @endif
                                <div class="pbmit-service-btn-wrapper">
                                    <div class="pbmit-service-btn-wrapper-inner pbmit-btn-hover pbmit-service-btn">
                                        <a class="pbmit-button-inner pbmit-button" href="{{ $itemUrl }}" title="{{ $item['title'] }}">
                                            <span class="pbmit-icon-hover"></span>
                                            <span class="pbmit-button-content-wrapper">
                                                <span class="pbmit-button-text">{{ $websiteContent->practiceCareButtonLabel($item) }}</span>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                                <div class="pbminfotech-box-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @if(filled($careCta['title'] ?? null) || filled($careCta['button_label'] ?? null))
                    <div class="col-md-6 col-lg-4 pbmit-ele pbmit-info-area">
                        <div class="pbmit-title-button-wrapper">
                            @if(filled($careCta['smiliz_icon'] ?? null))
                            <div class="pbmit-info-icon">
                                <i class="pbmit-smiliz-icon {{ $careCta['smiliz_icon'] }}" aria-hidden="true"></i>
                            </div>
                            @endif
                            @if(filled($careCta['title'] ?? null))
                            <div class="pbmit-info-heading">
                                <h3>{{ $careCta['title'] }}</h3>
                            </div>
                            @endif
                            @if(filled($careCta['button_label'] ?? null))
                            <div class="pbmit-ihbox-btn">
                                <a href="{{ $websiteContent->practiceCareCtaUrl($content, $loginUrl) }}" class="pbmit-btn white">
                                    <span class="pbmit-icon-hover"></span>
                                    <span class="pbmit-button-content-wrapper">
                                        <span class="pbmit-button-text">{{ $careCta['button_label'] }}</span>
                                    </span>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</section>
@endif

@endif
