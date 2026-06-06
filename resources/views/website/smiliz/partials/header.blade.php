<header class="site-header pbmit-header-style-1" id="masthead">
    <div class="pbmit-sticky-header pbmit-header-sticky-yes pbmit-sticky-bg-color-white pbmit-sticky-header-mobile-yes"></div>
    <div class="pbmit-main-header-area pbmit-infostack-header">
        <div class="container p-0">
            <div class="pbmit-infostack-header-inner d-flex justify-content-between align-items-center">
                <div class="site-branding pbmit-logo-area lineup-header-logo">
                    @if(empty($currentPageKey))
                    <h1 class="site-title">
                        <a href="{{ $websiteHomeUrl ?? route('website.home') }}">
                            @include('website.smiliz.partials.logo-img')
                        </a>
                    </h1>
                    @else
                    <div class="site-title">
                        <a href="{{ $websiteHomeUrl ?? route('website.home') }}">
                            @include('website.smiliz.partials.logo-img')
                        </a>
                    </div>
                    @endif
                </div>
                @if(filled($content['contact']['phone']) || filled($content['contact']['email']) || filled($content['contact']['address']))
                <div class="pbmit-header-info ms-auto">
                    <div class="pbmit-header-info-inner">
                        @if(filled($content['contact']['phone']))
                        <div class="pbmit-header-box pbmit-header-box-1">
                            <a href="tel:{{ preg_replace('/\s+/', '', $content['contact']['phone']) }}">
                                <div class="pbmit-header-box-icon">
                                    <div class="pbmit-icon-type-icon">
                                        <div class="pbmit-header-icon-wrap">
                                            <svg height="512" viewBox="0 0 512 512" width="512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <g>
                                                    <path d="m256 30a226.06 226.06 0 0 1 88 434.25 226.06 226.06 0 0 1 -176-416.5 224.5 224.5 0 0 1 88-17.75m0-30c-141.38 0-256 114.62-256 256s114.62 256 256 256 256-114.62 256-256-114.62-256-256-256z"></path>
                                                    <path d="m330.69 393.87c-14.87-1-35.83-6.13-56.29-13.45-72.14-25.82-142.53-94.61-157.49-190.83-2.66-17.13.14-32.78 13.12-45.52 4.35-4.26 8.22-9 12.47-13.36 16-16.47 39.38-16.89 55.95-1.07 5.25 5 10.59 9.93 15.71 15.09a38.07 38.07 0 0 1 1.37 52.79c-4 4.44-8.2 8.66-12.42 12.87-4.61 4.6-10.34 7.24-16.49 9.16-7.59 2.38-9 5.56-5.55 12.81q32.7 68.49 102.37 98.63c6.21 2.68 9.08 1.47 11.58-4.69 5.48-13.51 15.53-23.36 27.08-31.32 13.07-9 31.79-7 44.17 3.64a263.23 263.23 0 0 1 19.43 18.5 38.22 38.22 0 0 1 -.05 52.25c-1.93 2.1-3.92 4.15-5.77 6.31-11.14 12.95-25.27 19.01-49.19 18.19z"></path>
                                                </g>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="pbmit-header-content-wrap">
                                    <span class="pbmit-header-box-title">{{ __('website.need_to_talk') }}</span>
                                    <span class="pbmit-header-box-content">{{ $content['contact']['phone'] }}</span>
                                </div>
                            </a>
                        </div>
                        @endif
                        @if(filled($content['contact']['email']))
                        <div class="pbmit-header-box pbmit-header-box-2">
                            <a href="mailto:{{ $content['contact']['email'] }}">
                                <div class="pbmit-header-box-icon">
                                    <div class="pbmit-icon-type-icon">
                                        <div class="pbmit-header-icon-wrap">
                                            <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="m59.98 13.29c-.01-.01-.02-.03-.03-.04-.05-.05-.1-.1-.16-.14-.01 0-.01-.01-.02-.01-1.21-1.13-2.84-1.81-4.61-1.81h-46.32c-1.77 0-3.4.68-4.61 1.81-.01 0-.01.01-.02.01-.06.04-.11.09-.16.14-.01.01-.02.03-.03.04-.01 0-.01.01-.01.01-1.24 1.24-2.01 2.94-2.01 4.83v27.74c0 1.89.77 3.59 2.01 4.83 0 0 0 .01.01.01.008.008.018.017.026.025.003.003.004.008.007.011.006.006.014.008.02.014.044.043.087.087.138.13.01 0 .01.01.02.01 1.21 1.13 2.84 1.81 4.61 1.81h46.32c1.77 0 3.39-.68 4.6-1.8.041-.031.079-.071.119-.108.025-.022.056-.031.079-.056.01-.011.012-.025.021-.035.003-.003.008-.007.011-.01.02-.01.03-.03.04-.04 1.22-1.23 1.97-2.92 1.97-4.79v-27.74c0-1.89-.77-3.59-2.01-4.83 0 0 0-.01-.01-.01zm.02 32.58c0 1.02-.32 1.98-.87 2.76l-18.145-16.635.006-.005 18.14-16.62c.55.78.87 1.74.87 2.76v27.74zm-55.125 2.765s-.003-.003-.005-.005c-.55-.78-.87-1.74-.87-2.76v-27.74c0-1.02.32-1.98.87-2.76l18.15 16.63zm50.285 2.075h-46.32c-.92 0-1.77-.25-2.5-.7 0 0-.002-.001-.003-.002l18.161-16.65.002.002 6.82 6.25c.2.17.44.26.68.26s.48-.09.68-.26l6.82-6.25.007-.006 18.156 16.644s-.001.001-.002.002c-.73.46-1.58.71-2.5.71z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="pbmit-header-content-wrap">
                                    <span class="pbmit-header-box-title">{{ __('website.email_address') }}</span>
                                    <span class="pbmit-header-box-content">{{ $content['contact']['email'] }}</span>
                                </div>
                            </a>
                        </div>
                        @endif
                        @if(filled($content['contact']['address']))
                        <div class="pbmit-header-box pbmit-header-box-3 lineup-header-location">
                            <a href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($content['contact']['address']) }}" target="_blank" rel="noopener noreferrer">
                                <div class="pbmit-header-box-icon">
                                    <div class="pbmit-icon-type-icon">
                                        <div class="pbmit-header-icon-wrap">
                                            <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="m43.7 24.66c0-6.44-5.25-11.69-11.7-11.69s-11.7 5.25-11.7 11.69 5.25 11.7 11.7 11.7 11.7-5.25 11.7-11.7z"></path>
                                                <path d="m31.33 61.75c.19.17.43.25.67.25s.48-.08.67-.25c.89-.81 21.99-19.88 21.99-37.09 0-12.49-10.16-22.66-22.66-22.66s-22.66 10.17-22.66 22.66c0 17.21 21.1 36.28 21.99 37.09zm.67-57.75c11.39 0 20.66 9.27 20.66 20.66 0 14.66-17.05 31.56-20.66 34.98-3.62-3.42-20.66-20.31-20.66-34.98 0-11.39 9.27-20.66 20.66-20.66z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="pbmit-header-content-wrap">
                                    <span class="pbmit-header-box-title">{{ __('website.main_location') }}</span>
                                    <span class="pbmit-header-box-content">{{ $content['contact']['address'] }}</span>
                                </div>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        <div class="pbmit-header-menu">
            <div class="pbmit-header-menu-area-wrapper pbmit-header-wrapper">
                <div class="pbmit-header-menu-area">
                    <div class="container p-0">
                        <div class="pbmit-header-content d-flex justify-content-between align-items-center">
                            <div class="site-navigation">
                                <nav class="main-menu pbmit-navbar">
                                    <div>
                                        <ul id="pbmit-top-menu" class="navigation clearfix">
                                            <li @class(['active' => empty($currentPageKey)])>
                                                <a href="{{ $websiteHomeUrl ?? route('website.home') }}">{{ __('website.home') }}</a>
                                            </li>
                                            @foreach($navMenu ?? [] as $item)
                                                @if(count($item['children']) === 1)
                                                <li @class(['active' => ($currentPageKey ?? '') === $item['children'][0]['key']])>
                                                    <a href="{{ $item['children'][0]['url'] }}">{{ $item['children'][0]['label'] }}</a>
                                                </li>
                                                @else
                                                <li class="dropdown">
                                                    <a href="{{ $item['children'][0]['url'] ?? '#' }}" aria-haspopup="true">{{ $item['label'] }}</a>
                                                    <ul>
                                                        @foreach($item['children'] as $child)
                                                        <li @class(['active' => ($currentPageKey ?? '') === $child['key']])>
                                                            <a href="{{ $child['url'] }}">{{ $child['label'] }}</a>
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                                @endif
                                            @endforeach
                                            @include('website.smiliz.partials.language-switcher-nav')
                                        </ul>
                                    </div>
                                </nav>
                            </div>
                            <div class="pbmit-right-box d-flex align-items-center">
                                @if(filled($content['contact']['address']))
                                <div class="lineup-header-location-mobile d-lg-none">
                                    <a href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($content['contact']['address']) }}" target="_blank" rel="noopener noreferrer" class="lineup-header-location-mobile__link">
                                        <span class="lineup-header-location-mobile__icon" aria-hidden="true">
                                            <i class="pbmit-base-icon-location-1"></i>
                                        </span>
                                        <span class="lineup-header-location-mobile__text">{{ $content['contact']['address'] }}</span>
                                    </a>
                                </div>
                                @endif
                                <div class="lineup-header-lang-desktop d-none d-xl-inline-flex">
                                    @include('website.smiliz.partials.language-switcher')
                                </div>
                                <div class="pbmit-button-box">
                                    <a class="pbmit-btn" href="{{ $loginUrl }}">
                                        <span class="pbmit-icon-hover"></span>
                                        <span class="pbmit-button-content-wrapper">
                                            <span class="pbmit-button-text">{{ $content['hero']['cta_label'] }}</span>
                                        </span>
                                    </a>
                                </div>
                                <div class="pbmit-burger-menu-wrapper">
                                    <button class="nav-menu-toggle" id="menu-toggle" type="button" aria-controls="pbmit-top-menu" aria-expanded="false" aria-label="{{ __('website.menu_toggle') }}">
                                        <i class="pbmit-base-icon-menu-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(empty($currentPageKey))
    @if(($content['hero']['type'] ?? 'slider') === 'video')
    <div class="pbmit-slider-area pbmit-slider-one lineup-hero-video">
        <div class="pbmit-slider-item swiper-slide-active">
            <div class="pbmit-slider-bg lineup-hero-video__bg">
                <video class="lineup-hero-video__media" autoplay muted loop playsinline preload="metadata" aria-hidden="true">
                    <source src="{{ $websiteContent->heroVideoUrl() }}" type="video/mp4">
                </video>
                <div class="lineup-hero-video__shade" aria-hidden="true"></div>
            </div>
            <div class="container">
                <div class="row align-items-center">
                    @include('website.smiliz.partials.hero-video-content', [
                        'eyebrow' => $content['hero']['eyebrow'],
                        'title' => $content['hero']['title'],
                        'description' => $content['hero']['subtitle'],
                        'ctaLabel' => $content['hero']['cta_label'],
                        'ctaUrl' => $loginUrl,
                    ])
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="pbmit-slider-area pbmit-slider-one">
        <div class="swiper-slider" data-autoplay="true" data-loop="true" data-dots="false" data-arrows="true" data-allow-touch="false" data-columns="1" data-margin="0" data-effect="fade">
            <div class="swiper-wrapper">
                @foreach($content['slides'] as $slide)
                <div class="swiper-slide">
                    <div class="pbmit-slider-item">
                        <div class="pbmit-slider-bg" style="background-image: url({{ $websiteContent->slideImageUrl($slide) }});"></div>
                        <div class="container">
                            <div class="row">
                                @include('website.smiliz.partials.hero-circle', [
                                    'eyebrow' => $slide['eyebrow'] ?? '',
                                    'title' => $slide['title'] ?? '',
                                    'description' => $content['hero']['subtitle'] ?? '',
                                    'ctaLabel' => $slide['cta_label'] ?? $content['hero']['cta_label'],
                                    'ctaUrl' => $loginUrl,
                                ])
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    @endif
</header>
