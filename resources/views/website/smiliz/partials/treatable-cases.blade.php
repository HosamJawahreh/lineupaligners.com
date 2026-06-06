@if($content['sections']['treatable_cases'] ?? true)
<section class="section-lg pbmit-bg-color-light bottom-radius position-relative" id="treatable-cases">
    <div class="container" data-aos="fade-up" data-aos-duration="800">
        <div class="pbmit-heading-subheading style-2 animation-style2 text-center">
            <h4 class="pbmit-subtitle">{{ $content['treatable_cases']['subtitle'] }}</h4>
            <h2 class="pbmit-title">{!! nl2br(e($content['treatable_cases']['title'])) !!}</h2>
            @if(filled($content['treatable_cases']['intro'] ?? ''))
            <div class="pbmit-heading-desc">{{ $content['treatable_cases']['intro'] }}</div>
            @endif
        </div>
        <div class="swiper-slider pb-4 m-t-30" data-autoplay="true" data-loop="true" data-dots="false" data-arrows-class="treatable-cases-arrow" data-allow-touch="true" data-arrows="true" data-columns="3" data-margin="30" data-effect="slide">
            <div class="swiper-wrapper">
                @foreach($content['treatable_cases']['items'] as $item)
                <article class="pbmit-service-style-1 swiper-slide">
                    <div class="pbminfotech-post-item">
                        <div class="pbmit-box-content-wrap">
                            <div class="pbmit-content-box">
                                <h3 class="pbmit-service-title">{{ $item['title'] }}</h3>
                                <div class="pbmit-service-description">
                                    <p>{{ $item['description'] }}</p>
                                </div>
                            </div>
                            <div class="pbmit-service-image-wrapper">
                                <div class="pbmit-service-image-inner">
                                    <div class="pbmit-featured-img-wrapper">
                                        <div class="pbmit-featured-wrapper">
                                            <img src="{{ $websiteContent->treatableImageUrl($item) }}" class="img-fluid" alt="{{ $item['title'] }}" loading="lazy" decoding="async">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
        <div class="d-inline-flex treatable-cases-arrow"></div>
    </div>
</section>
@endif
