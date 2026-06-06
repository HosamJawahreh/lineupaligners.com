@if($content['sections']['services'] ?? true)

@if(($content['template'] ?? '') !== 'smiliz-homepage-2')

<section class="section-xl bottom-radius pbmit-bg-color-light position-relative" id="services" style="z-index: 1;">

    <div class="container" data-aos="fade-up" data-aos-duration="800">

        <div class="pbmit-heading-subheading style-2 animation-style2">

            <div>

                <h4 class="pbmit-subtitle">{{ $content['platform']['subtitle'] }}</h4>

                <h2 class="pbmit-title">{!! nl2br(e($content['platform']['title'])) !!}</h2>

            </div>

            @if(filled($content['platform']['intro']))

            <div class="pbmit-heading-desc">{{ $content['platform']['intro'] }}</div>

            @endif

        </div>

        <div class="swiper-slider pb-4" data-autoplay="true" data-loop="true" data-dots="false" data-arrows-class="service-arrow" data-allow-touch="true" data-arrows="true" data-columns="4" data-margin="30" data-effect="slide">

            <div class="swiper-wrapper">

                @foreach($content['features'] as $feature)

                <article class="pbmit-service-style-1 swiper-slide">

                    <div class="pbminfotech-post-item">

                        <div class="pbmit-box-content-wrap">

                            <div class="pbmit-content-box">

                                <h3 class="pbmit-service-title">

                                    <a href="{{ $websiteContent->serviceLinkUrl($feature, $loginUrl) }}">{{ $feature['title'] }}</a>

                                </h3>

                                <div class="pbmit-service-description">

                                    <p>{{ $feature['description'] }}</p>

                                </div>

                            </div>

                            <div class="pbmit-service-image-wrapper">

                                <div class="pbmit-service-image-inner">

                                    <div class="pbmit-featured-img-wrapper">

                                        <div class="pbmit-featured-wrapper">

                                            <a href="{{ $websiteContent->serviceLinkUrl($feature, $loginUrl) }}">

                                                <img src="{{ $websiteContent->featureImageUrl($feature, $loop->index) }}" class="img-fluid" alt="{{ $feature['title'] }}" loading="lazy" decoding="async">

                                            </a>

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

        <div class="d-inline-flex service-arrow"></div>

        @if(filled($content['platform']['cta_label']))

        <div class="text-center m-t-30">

            <a href="{{ $websiteContent->servicesCtaUrl($content, $loginUrl) }}" class="pbmit-btn">

                <span class="pbmit-icon-hover"></span>

                <span class="pbmit-button-content-wrapper">

                    <span class="pbmit-button-text">{{ $content['platform']['cta_label'] }}</span>

                </span>

            </a>

        </div>

        @endif

    </div>

</section>

@endif

@endif

