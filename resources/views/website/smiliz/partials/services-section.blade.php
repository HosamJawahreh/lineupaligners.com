@if($content['sections']['services'] ?? true)

<section class="section-xl bottom-radius pbmit-bg-color-light position-relative lineup-why-lineup-section" id="services">

    <div class="container" data-aos="fade-up" data-aos-duration="800">

        <div class="pbmit-heading-subheading style-2 animation-style2 lineup-why-lineup-section__head">

            <div>

                <h4 class="pbmit-subtitle">{{ $content['platform']['subtitle'] }}</h4>

                <h2 class="pbmit-title">{!! nl2br(e($content['platform']['title'])) !!}</h2>

            </div>

            @if(filled($content['platform']['intro']))

            <div class="pbmit-heading-desc">{{ $content['platform']['intro'] }}</div>

            @endif

        </div>

        <div class="lineup-why-lineup-grid">

            @foreach($content['features'] as $feature)

            @php
                $cardUrl = $websiteContent->serviceLinkUrl($feature, $loginUrl);
                $buttonLabel = $websiteContent->serviceButtonLabel($feature, __('website.learn_more'));
                $iconValue = trim($feature['smiliz_icon'] ?? $feature['icon'] ?? '');
                $smilizIcon = str_starts_with($iconValue, 'pbmit-smiliz-icon') ? $iconValue : '';
            @endphp

            <article class="lineup-why-lineup-card">

                <div class="lineup-why-lineup-card__media">

                    <a href="{{ $cardUrl }}" tabindex="-1" aria-hidden="true">

                        <img src="{{ $websiteContent->featureImageUrl($feature, $loop->index) }}" class="img-fluid" alt="" loading="lazy" decoding="async">

                    </a>

                    @if($smilizIcon !== '')
                    <span class="lineup-why-lineup-card__icon" aria-hidden="true">
                        <i class="pbmit-smiliz-icon {{ $smilizIcon }}"></i>
                    </span>
                    @endif

                </div>

                <div class="lineup-why-lineup-card__body">

                    <h3 class="lineup-why-lineup-card__title">
                        <a href="{{ $cardUrl }}">{{ $feature['title'] }}</a>
                    </h3>

                    <p class="lineup-why-lineup-card__desc">{{ $feature['description'] }}</p>

                    <a href="{{ $cardUrl }}" class="lineup-why-lineup-card__link">
                        <span>{{ $buttonLabel }}</span>
                        <i class="pbmit-base-icon-right-arrow" aria-hidden="true"></i>
                    </a>

                </div>

            </article>

            @endforeach

        </div>

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
