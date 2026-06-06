@if($content['sections']['portfolio'] ?? true)
<section class="pbmit-bg-color-secondary bottom-radius transform-top-sec portfolio-section-two lineup-cases-section" id="cases">
    <div class="container" data-aos="fade-up" data-aos-duration="800">
        <div class="swiper-slider" data-autoplay="true" data-loop="{{ count($portfolioItems) > 2 ? 'true' : 'false' }}" data-dots="true" data-arrows="true" data-columns="2" data-allow-touch="false" data-margin="30" data-effect="slide">
            <div class="pbmit-heading-subheading style-2 animation-style2">
                <div>
                    <h4 class="pbmit-subtitle">{{ $content['treatments']['subtitle'] }}</h4>
                    <h2 class="pbmit-title">{!! nl2br(e($content['treatments']['title'])) !!}</h2>
                </div>
                <div class="pbmit-heading-desc">{{ $content['treatments']['intro'] }}</div>
            </div>
            <div class="swiper-wrapper">
                @foreach($portfolioItems as $item)
                <article class="pbmit-portfolio-style-2 swiper-slide">
                    <div class="pbminfotech-post-content">
                        <div class="row">
                            @if(!empty($item['before_url']) && !empty($item['after_url']))
                            <div class="pbmit-ele-before-after-inner">
                                <img src="{{ $item['before_url'] }}" class="pbmit-before-image" alt="{{ __('website.before') }} — {{ $item['title'] }}">
                                <img src="{{ $item['after_url'] }}" class="pbmit-after-image pbmit-hide" alt="{{ __('website.after') }} — {{ $item['title'] }}">
                            </div>
                            @elseif(!empty($item['after_url']))
                            <div class="pbmit-ele-before-after-inner">
                                <img src="{{ $item['after_url'] }}" class="pbmit-after-image" alt="{{ $item['title'] }}">
                            </div>
                            @elseif(!empty($item['before_url']))
                            <div class="pbmit-ele-before-after-inner">
                                <img src="{{ $item['before_url'] }}" class="pbmit-before-image" alt="{{ $item['title'] }}">
                            </div>
                            @endif
                            <div class="pbminfotech-box-content col-md-6 col-lg-6">
                                <div class="pbminfotech-box-content-inner">
                                    @if(!empty($item['category']))
                                    <div class="pbmit-port-cat"><span>{{ $item['category'] }}</span></div>
                                    @endif
                                    <h3 class="pbmit-portfolio-title">
                                        <span>{{ $item['title'] }}</span>
                                    </h3>
                                </div>
                                @if(!empty($item['patient_label']))
                                <div class="pbmit-portfolio-subtitle"><span>{{ $item['patient_label'] }}</span></div>
                                @endif
                                @if(!empty($item['summary']) || !empty($item['treatment_months']))
                                <div class="pbmit-portfolio-description">
                                    <p>
                                        @if(!empty($item['treatment_months'])){{ $item['treatment_months'] }} {{ __('website.months') }} · @endif
                                        {{ $item['summary'] }}
                                    </p>
                                </div>
                                @endif
                                <div class="pbmit-btn-hover pbmit-portfolio-btn">
                                    <a href="{{ !empty($item['url']) ? $item['url'] : '#cases' }}" class="pbmit-btn">
                                        <span class="pbmit-icon-hover"></span>
                                        <span class="pbmit-button-content-wrapper">
                                            <span class="pbmit-button-text">{{ __('website.more_details') }}</span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
