@extends('website.smiliz.layout')

@section('title', __('website.not_found_title'))

@section('meta_description', __('website.not_found_description'))

@section('smiliz-body')
@include('website.smiliz.partials.header')

<div class="page-content pbmit-bg-color-white">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center lineup-not-found">
                <p class="lineup-not-found__code" aria-hidden="true">404</p>
                <h1 class="pbmit-title">{{ __('website.not_found_title') }}</h1>
                <p class="pbmit-heading-desc">{{ __('website.not_found_body') }}</p>
                <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                    <a class="pbmit-btn" href="{{ $websiteHomeUrl ?? route('website.home') }}">
                        <span class="pbmit-button-content-wrapper">
                            <span class="pbmit-button-text">{{ __('website.not_found_home') }}</span>
                        </span>
                    </a>
                    @if(filled($content['contact']['phone'] ?? null))
                    <a class="pbmit-btn pbmit-btn-outline" href="tel:{{ preg_replace('/\s+/', '', $content['contact']['phone']) }}">
                        <span class="pbmit-button-content-wrapper">
                            <span class="pbmit-button-text">{{ __('website.need_to_talk') }}</span>
                        </span>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('website.smiliz.partials.footer')
@endsection
