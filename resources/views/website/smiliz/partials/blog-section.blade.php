@if($content['sections']['blog'] ?? false)

@php
    $blogPosts = collect($content['blog']['items'] ?? [])->filter(fn ($post) => filled($post['title'] ?? null));
    $categoryUrl = $websiteContent->blogCategoryUrl($websiteLocale ?? null);
@endphp

@if($blogPosts->isNotEmpty())
<section class="section-xl pbmit-bg-color-white bottom-radius lineup-blog-section" id="blog">
    <div class="container" data-aos="fade-in" data-aos-duration="800">
        <div class="pbmit-heading-subheading text-center animation-style2">
            <h4 class="pbmit-subtitle">{{ $content['blog']['subtitle'] }}</h4>
            <h2 class="pbmit-title">{!! nl2br(e($content['blog']['title'])) !!}</h2>
        </div>
        <div class="swiper-slider" data-autoplay="false" data-loop="{{ $blogPosts->count() > 3 ? 'true' : 'false' }}" data-dots="false" data-allow-touch="true" data-arrows="false" data-columns="3" data-margin="30" data-effect="slide">
            <div class="swiper-wrapper">
                @foreach($blogPosts as $post)
                @php
                    $postUrl = $websiteContent->blogPostUrl($post, $websiteLocale ?? null);
                    $imageUrl = $websiteContent->blogPostImageUrl($post);
                @endphp
                <article class="pbmit-blog-style-1 swiper-slide">
                    <div class="post-item">
                        <div class="pbminfotech-box-content">
                            <div class="pbmit-featured-container">
                                <div class="pbmit-featured-container-inner">
                                    <div class="pbmit-featured-img-wrapper">
                                        <div class="pbmit-featured-wrapper">
                                            <img src="{{ $imageUrl }}" class="img-fluid" alt="{{ $post['title'] }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="pbmit-meta-wraper-inner">
                                    <div class="pbmit-meta-wraper">
                                        @if(filled($post['category'] ?? null))
                                        <div class="pbmit-meta-category-wrapper pbmit-meta-line">
                                            <span class="pbmit-meta-category">
                                                <a href="{{ $categoryUrl }}" rel="category tag">{{ $post['category'] }}</a>
                                            </span>
                                        </div>
                                        @endif
                                        @if(filled($post['date'] ?? null))
                                        <div class="pbmit-meta-date-wrapper pbmit-meta-line">
                                            <span class="pbmit-post-date">{{ $post['date'] }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="pbmit-content-wrapper">
                                <h3 class="pbmit-post-title">
                                    <a href="{{ $postUrl }}">{{ $post['title'] }}</a>
                                </h3>
                                @if(filled($post['excerpt'] ?? null))
                                <div class="pbminfotech-box-desc">{{ $post['excerpt'] }}</div>
                                @endif
                                <div class="pbmit-btn-hover pbmit-blog-btn">
                                    <a class="pbmit-button-inner pbmit-button" href="{{ $postUrl }}" title="{{ $post['title'] }}">
                                        <span class="pbmit-icon-hover"></span>
                                        <span class="pbmit-button-content-wrapper">
                                            <span class="pbmit-button-text">{{ __('website.read_more') }}</span>
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

@endif
