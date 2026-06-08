@extends('website.smiliz.layout')

@section('title', $content['seo']['meta_title'] ?? $projectName)

@section('smiliz-body')
@include('website.smiliz.partials.header')

<div class="page-content pbmit-bg-color-white">

    @if($content['sections']['about'] ?? true)
    <section class="section-lg pbmit-bg-color-light bottom-radius position-relative" id="about">
        <div class="container">
            <div class="row align-items-center" data-aos="fade-up" data-aos-duration="800">
                <div class="col-md-6 full-width-1200">
                    <div class="about-two-leftbox">
                        <div class="teeth-bg-img">
                            <img src="{{ $websiteContent->smilizAsset('images/homepage-2/bg/teeth-bg.png') }}" class="img-fluid" alt="">
                        </div>
                        <div class="text-center">
                            <img src="{{ $websiteContent->aboutImageUrl($content['template'] ?? null) }}" class="mask-img img-fluid" alt="">
                        </div>
                        <div class="fid-style-box">
                            <div class="pbminfotech-ele-fid-style-2">
                                <div class="pbmit-fld-contents">
                                    <h4 class="pbmit-fid-inner">
                                        <span class="pbmit-number-rotate numinate" data-appear-animation="animateDigits" data-from="0" data-to="{{ $content['about']['years'] }}" data-interval="1">{{ $content['about']['years'] }}</span>
                                        <span class="pbmit-fid"><span>+</span></span>
                                    </h4>
                                    <div class="pbmit-fid-desc">
                                        <div class="pbmit-heading-desc">{!! nl2br(e($content['about']['years_label'])) !!}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 full-width-1200">
                    <div class="about-two-rightbox">
                        <div class="pbmit-heading-subheading animation-style2">
                            <h4 class="pbmit-subtitle">{{ $content['about']['subtitle'] }}</h4>
                            <h2 class="pbmit-title">{{ $content['about']['title'] }}</h2>
                            <div class="pbmit-heading-desc">{{ $content['about']['body'] }}</div>
                        </div>
                        <div class="ihbox-style-area">
                            <div class="row">
                                @foreach($content['about']['highlights'] as $highlight)
                                <div class="col-md-6{{ $loop->index > 0 ? ' mt-md-0 mt-4' : '' }}">
                                    <div class="pbmit-ihbox-style-1">
                                        <div class="pbmit-ihbox-box">
                                            <span class="lineup-highlight-icon" aria-hidden="true"><i class="pbmit-smiliz-icon pbmit-smiliz-icon-check-mark"></i></span>
                                            <h2 class="pbmit-element-title">{{ $highlight['title'] }}</h2>
                                        </div>
                                        <div class="pbmit-heading-desc">{{ $highlight['description'] }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <a href="{{ $loginUrl }}" class="pbmit-btn pbmit-hover-global">
                            <span class="pbmit-icon-hover"></span>
                            <span class="pbmit-button-content-wrapper"><span class="pbmit-button-text">{{ $content['hero']['cta_label'] }}</span></span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="ihbox-style-2-bottom-area" data-aos="fade-left" data-aos-duration="800">
                <div class="row">
                    @foreach($content['about']['pills'] as $pill)
                    <article class="pbmit-miconheading-style-2 col-md-6 col-lg-4 col-xl-3">
                        <div class="pbmit-ihbox-style-2">
                            <div class="pbmit-ihbox-box">
                                <div class="pbmit-ihbox-content-wrap">
                                    <h2 class="pbmit-element-title">{{ $pill['title'] }}</h2>
                                    <div class="pbmit-heading-desc">{{ $pill['description'] }}</div>
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

    @include('website.smiliz.partials.services-section')

    @include('website.smiliz.partials.process-section')

    @if($content['sections']['stats'] ?? true)
    <section class="section-xxl bottom-radius pbmit-bg-color-light position-relative">
        <div class="container">
            <div class="pbmit-heading-subheading text-center animation-style2">
                <h4 class="pbmit-subtitle">{{ $content['stats_section']['subtitle'] }}</h4>
                <h2 class="pbmit-title">{!! nl2br(e($content['stats_section']['title'])) !!}</h2>
            </div>
            <div class="row" data-aos="fade-up" data-aos-duration="800">
                @foreach(array_slice($content['stats'], 0, 2) as $stat)
                <div class="col-md-12 col-xl-4">
                    <div class="pbmit-bg-color-white info-box-first p-4">
                        <div class="pbminfotech-ele-fid-style-4">
                            <div class="pbmit-fld-contents">
                                <h4 class="pbmit-fid-inner">
                                    <span class="pbmit-number-rotate numinate" data-appear-animation="animateDigits" data-from="0" data-to="{{ preg_replace('/[^0-9]/', '', $stat['value']) }}" data-interval="5">{{ preg_replace('/[^0-9]/', '', $stat['value']) }}</span>
                                    <span class="pbmit-fid"><sup>{{ preg_replace('/[0-9]/', '', $stat['value']) }}</sup></span>
                                </h4>
                                <div class="pbmit-fid-desc"><div class="pbmit-heading-desc">{{ $stat['label'] }}</div></div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                <div class="col-md-12 col-xl-4">
                    <div class="info-box-fourth pbmit-bg-color-global p-4">
                        <div class="pbmit-custom-title">
                            <h2 class="pbmit-title">{{ $content['stats_section']['cta_title'] }}</h2>
                        </div>
                        <a href="{{ $loginUrl }}" class="pbmit-btn white">
                            <span class="pbmit-icon-hover"></span>
                            <span class="pbmit-button-content-wrapper"><span class="pbmit-button-text">{{ $content['stats_section']['cta_label'] }}</span></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    @if($content['sections']['partner_cta'] ?? true)
    <section class="pbmit-bg-color-light">
        <section class="appointment-two-bg bottom-radius transform-top-sec">
            <div class="container">
                <div class="row g-0 align-items-center">
                    <div class="col-md-12 col-xl-8">
                        <div class="appointment-two-leftbox p-4">
                            <div class="pbmit-custom-title">
                                <h2 class="pbmit-title">“{{ $content['partner_cta']['quote'] }}”</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-xl-4" data-aos="zoom-in" data-aos-duration="800">
                        <div class="appointment-two-rightbox lineup-partner-panel">
                            <h4>{{ $content['partner_cta']['title'] }}</h4>
                            <p>{{ $content['partner_cta']['body'] }}</p>
                            <a href="{{ $loginUrl }}" class="pbmit-btn submit w-100 text-center">
                                <span class="pbmit-icon-hover"></span>
                                <span class="pbmit-button-content-wrapper"><span class="pbmit-button-text">{{ $content['partner_cta']['cta_label'] }}</span></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
    @endif

    @include('website.smiliz.partials.our-work')

    @if($content['sections']['faq'] ?? true)
    <section class="section-md pbmit-bg-color-light position-relative lineup-faq-section" id="faq">
        @include('website.smiliz.partials.section-grid-atmosphere')
        <div class="container position-relative">
            <div class="row">
                <div class="col-md-5 pbmit-sticky-column">
                    <div class="pbmit-heading-subheading">
                        <h4 class="pbmit-subtitle">{{ $content['faq']['subtitle'] }}</h4>
                        <h2 class="pbmit-title">{!! nl2br(e($content['faq']['title'])) !!}</h2>
                    </div>
                    @if(Route::has('website.page.faq'))
                    <div class="m-t-20">
                        <a href="{{ route('website.page.faq') }}" class="pbmit-btn">
                            <span class="pbmit-icon-hover"></span>
                            <span class="pbmit-button-content-wrapper"><span class="pbmit-button-text">{{ __('website.view_all_faqs') }}</span></span>
                        </a>
                    </div>
                    @endif
                </div>
                <div class="col-md-7">
                    @include('website.smiliz.partials.faq-accordion', [
                        'accordionId' => 'homepageFaq',
                        'items' => $content['faq']['items'],
                    ])
                </div>
            </div>
        </div>
    </section>
    @endif

    @include('website.smiliz.partials.blog-section')

    @if($content['sections']['cta_banner'] ?? true)
    <section class="pbmit-bg-color-light">
        <section class="about-us-one-bg pbmit-bg-color-global transform-top-sec bottom-radius">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-12 col-xl-3">
                        <div class="pbmit-ihbox-style-5">
                            <div class="pbmit-ihbox-box">
                                <div class="pbmit-ihbox-contents">
                                    <h2 class="pbmit-element-title">{{ $content['cta_banner']['rating'] }}</h2>
                                    <div class="pbmit-heading-desc">{{ $content['cta_banner']['rating_label'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-xl-6">
                        <p class="about-us-one-subtitle">{{ $content['cta_banner']['subtitle'] }}</p>
                        <h2 class="about-us-one-title">{!! nl2br(e($content['cta_banner']['title'])) !!}</h2>
                    </div>
                    <div class="col-md-12 col-xl-3 text-xl-center">
                        <a href="{{ $loginUrl }}" class="pbmit-btn white">
                            <span class="pbmit-icon-hover"></span>
                            <span class="pbmit-button-content-wrapper"><span class="pbmit-button-text">{{ $content['cta_banner']['cta_label'] }}</span></span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </section>
    @endif

</div>

@include('website.smiliz.partials.footer')
@endsection
