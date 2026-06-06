@extends('website.smiliz.layout')

@section('title', $content['seo']['meta_title'] ?? $projectName)

@section('smiliz-body')
@include('website.smiliz.partials.header')

@php
    $aboutPhotoUrl = $websiteContent->aboutImageUrl($content['template'] ?? null);
    $customAboutPhoto = $websiteContent->hasCustomAboutImage();
    $aboutRightboxPhoto = $customAboutPhoto
        ? $aboutPhotoUrl
        : $websiteContent->smilizAsset('images/homepage-1/about-img-1.jpg');
@endphp

<div class="page-content pbmit-bg-color-white">

    @if($content['sections']['about'] ?? true)
    <section class="pbmit-bg-color-light">
        <section class="pbmit-bg-color-blackish about-section-one bottom-radius transform-top-sec" id="about">
            <div class="container">
                <div class="row" data-aos="fade-up" data-aos-duration="800">
                    @unless($customAboutPhoto)
                    <div class="about-bg1-img">
                        <img src="{{ $aboutPhotoUrl }}" class="img-fluid" alt="">
                    </div>
                    @endunless
                    <div class="col-md-6 full-width-1200">
                        <div class="pbmit-heading-subheading animation-style2">
                            <h4 class="pbmit-subtitle">{{ $content['about']['subtitle'] }}</h4>
                            <h2 class="pbmit-title">{{ $content['about']['title'] }}</h2>
                        </div>
                        <div class="row g-0 pt-3">
                            <div class="col-md-4">
                                <div class="pbminfotech-ele-fid-style-5">
                                    <div class="pbmit-fld-contents">
                                        <h3 class="pbmit-fid-title">{{ $content['about']['years_label'] }}</h3>
                                        <h4 class="pbmit-fid-inner">
                                            <span class="pbmit-number-rotate numinate" data-appear-animation="animateDigits" data-from="0" data-to="{{ $content['about']['years'] }}" data-interval="1">{{ $content['about']['years'] }}</span>
                                            <span class="pbmit-fid"><span>+</span></span>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="inner-content">
                                    <p>{{ $content['about']['body'] }}</p>
                                    <a href="{{ $loginUrl }}" class="pbmit-btn">
                                        <span class="pbmit-icon-hover"></span>
                                        <span class="pbmit-button-content-wrapper">
                                            <span class="pbmit-button-text">{{ $content['hero']['cta_label'] }}</span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 full-width-1200">
                        <div class="about-one-rightbox @if($customAboutPhoto) is-custom-photo @endif"
                             @if($customAboutPhoto) style="background-image: url('{{ $aboutPhotoUrl }}');" @endif>
                            <div class="about-one-rightbox__photo d-md-none" style="background-image: url('{{ $aboutRightboxPhoto }}');" aria-hidden="true"></div>
                            @unless($customAboutPhoto)
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
                            @endunless
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
    @endif

    @include('website.smiliz.partials.services-section')

    @include('website.smiliz.partials.process-section')

    @include('website.smiliz.partials.our-work')

    @if($content['sections']['faq'] ?? true)
    <section class="section-md pbmit-bg-color-light bottom-radius position-relative lineup-faq-section" id="faq">
        <div class="container">
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
