<?php

namespace App\Services;

use Illuminate\Support\Facades\View;

class SmilizHtmlRenderer
{
    /** @var array<string, mixed> */
    private array $renderContext = [];

    public function __construct(
        private SmilizPageRegistry $registry,
        private WebsiteContent $website,
        private WebsiteLocale $locale,
    ) {}

    /** @param  array<string, mixed>  $context */
    public function render(string $pageKey, array $context = []): array
    {
        $this->renderContext = $context;

        try {
            return $this->renderPage($pageKey);
        } finally {
            $this->renderContext = [];
        }
    }

    private function renderPage(string $pageKey): array
    {
        $page = $this->registry->find($pageKey);

        if (! $page) {
            abort(404);
        }

        $path = $this->registry->sourceDirectory().'/'.$page['file'];

        if (! is_file($path)) {
            abort(404);
        }

        $html = file_get_contents($path);
        $title = $this->extractTitle($html);
        $content = $this->extractBody($html);
        $content = $this->rewriteAssets($content);
        $content = $this->rewriteLinks($content);
        $content = $this->rewriteBreadcrumbs($content);
        $content = $this->injectTitleBarImage($content);
        $content = $this->prepareBeforeAfter($content);
        $content = $this->injectSiteContent($content, $pageKey);
        $content = $this->wireInquiryForms($content, $pageKey);
        $content = $this->applyLocaleTranslations($content, $pageKey);
        $content = $this->lazyLoadImages($content);
        $title = $this->localizedPageTitle($pageKey, $title);
        $description = $this->pageDescription($pageKey);

        return [
            'title' => $title,
            'description' => $description,
            'html' => $content,
            'page' => $page,
            'has_before_after' => $this->pageHasBeforeAfter($content),
        ];
    }

    private function extractTitle(string $html): string
    {
        if (preg_match('/<title>(.*?)<\/title>/is', $html, $matches)) {
            return trim(html_entity_decode(strip_tags($matches[1])));
        }

        return 'LineUp';
    }

    private function extractBody(string $html): string
    {
        if (preg_match('/<!-- Header Main Area End Here -->\s*(.*?)\s*<!-- footer -->/is', $html, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/<div class="page-content[^"]*"[^>]*>(.*?)<!-- Page Content End -->/is', $html, $matches)) {
            return '<div class="page-content">'.$matches[1].'</div>';
        }

        abort(500, 'Could not extract page content from Smiliz template.');
    }

    private function rewriteAssets(string $html): string
    {
        $assetBase = asset('assets/smiliz');

        $html = preg_replace_callback(
            '/\b(src|href)=["\'](?!https?:|\/|#|mailto:|tel:)(images\/[^"\']+)["\']/i',
            fn (array $m) => $m[1].'="'.$assetBase.'/'.$m[2].'"',
            $html
        );

        $html = preg_replace_callback(
            '/url\((["\']?)(?!https?:|\/|data:)(images\/[^)\'"]+)\1\)/i',
            fn (array $m) => 'url('.$assetBase.'/'.$m[2].')',
            $html
        );

        return $html;
    }

    private function rewriteBreadcrumbs(string $html): string
    {
        $homeUrl = e($this->locale->homeUrl());
        $homeLabel = e(__('website.home'));
        $projectName = e(\App\Models\Setting::projectName());

        $html = preg_replace(
            '/<a[^>]*class=["\']home["\'][^>]*>.*?<\/a>/is',
            '<a title="'.$homeLabel.'" href="'.$homeUrl.'" class="home"><span>'.$projectName.'</span></a>',
            $html
        ) ?? $html;

        return str_replace('>Smiliz<', '>'.$projectName.'<', $html);
    }

    private function injectTitleBarImage(string $html): string
    {
        if (! str_contains($html, 'pbmit-title-bar-wrapper')) {
            return $html;
        }

        $url = e($this->website->titleBarImageUrl());

        if (preg_match('/<div class="pbmit-title-bar-wrapper"[^>]*style="/i', $html)) {
            return preg_replace(
                '/(<div class="pbmit-title-bar-wrapper"[^>]*style="[^"]*?)background-image:\s*url\([^)]*\)/i',
                '$1background-image:url(\''.$url.'\')',
                $html,
                1
            ) ?? $html;
        }

        return preg_replace(
            '/(<div class="pbmit-title-bar-wrapper)(["\'])/i',
            '$1 style="background-image:url(\''.$url.'\')"$2',
            $html,
            1
        ) ?? $html;
    }

    private function prepareBeforeAfter(string $html): string
    {
        return preg_replace_callback(
            '/(<div class="pbmit-ele-before-after-inner"[^>]*>)(.*?)(<\/div>)/is',
            function (array $matches) {
                if (substr_count(strtolower($matches[2]), '<img') < 2) {
                    return $matches[0];
                }

                $inner = preg_replace_callback(
                    '/(<img[^>]*class=["\'][^"\']*pbmit-after-image)([^"\']*)(["\'])/i',
                    function (array $imgMatch) {
                        if (str_contains($imgMatch[2], 'pbmit-hide')) {
                            return $imgMatch[0];
                        }

                        return $imgMatch[1].$imgMatch[2].' pbmit-hide'.$imgMatch[3];
                    },
                    $matches[2]
                ) ?? $matches[2];

                return $matches[1].$inner.$matches[3];
            },
            $html
        ) ?? $html;
    }

    public function pageHasBeforeAfter(string $html): bool
    {
        return preg_match('/<div class="pbmit-ele-before-after-inner"[^>]*>.*?<img.*?<img/is', $html) === 1;
    }

    private function lazyLoadImages(string $html): string
    {
        return preg_replace_callback('/<img\b([^>]*)>/i', function (array $matches) {
            $attrs = $matches[1];

            if (stripos($attrs, 'loading=') !== false) {
                return $matches[0];
            }

            if (preg_match('/class=["\'][^"\']*(?:pbmit-before-image|pbmit-after-image|logo-img)[^"\']*["\']/i', $attrs)) {
                return $matches[0];
            }

            return '<img loading="lazy" decoding="async"'.$attrs.'>';
        }, $html) ?? $html;
    }

    public function pageDescription(string $pageKey): string
    {
        if ($this->locale->current() === 'ar') {
            $description = config('smiliz-pages-i18n-ar.page_descriptions.'.$pageKey);

            if (filled($description)) {
                return (string) $description;
            }
        }

        $description = config('smiliz-pages.page_descriptions.'.$pageKey);

        if (filled($description)) {
            return (string) $description;
        }

        return (string) ($this->website->all($this->locale->current())['seo']['meta_description'] ?? '');
    }

    private function rewriteLinks(string $html): string
    {
        $map = $this->registry->htmlLinkMap();

        return preg_replace_callback(
            '/\bhref=["\']([^"\']+\.html(?:#[^"\']*)?)["\']/i',
            function (array $matches) use ($map) {
                $href = $matches[1];
                $hash = '';

                if (str_contains($href, '#')) {
                    [$href, $hash] = explode('#', $href, 2);
                    $hash = '#'.$hash;
                }

                $target = $map[$href] ?? $map[basename($href)] ?? null;

                if ($target) {
                    return 'href="'.$target.$hash.'"';
                }

                return $matches[0];
            },
            $html
        );
    }

    private function injectSiteContent(string $html, string $pageKey): string
    {
        $content = $this->website->all($this->locale->current());

        if (str_starts_with($pageKey, 'contact')) {
            return $this->injectContactPage($html, $content);
        }

        if ($pageKey === 'service-details') {
            if (empty($this->renderContext['service_page'])) {
                return $this->injectServicesListing($html, $content);
            }

            return $this->injectServicePage($html, $content, $this->renderContext);
        }

        if ($pageKey === 'blog-classic') {
            return $this->injectBlogListing($html, $content);
        }

        if ($pageKey === 'portfolio-grid-col-4') {
            return $this->injectCaseStudyListing($html, $content);
        }

        if ($pageKey === 'blog-single-details') {
            return $this->injectBlogPage($html, $content, $this->renderContext);
        }

        if ($pageKey === 'case-study-style-1') {
            return $this->injectCaseStudyPage($html, $content, $this->renderContext);
        }

        if ($pageKey === 'about-us') {
            return $this->injectAboutPage($html, $content);
        }

        if ($pageKey === 'faq') {
            return $this->injectFaqPage($html, $content);
        }

        return $html;
    }

    /** @param  array<string, mixed>  $content */
    private function injectFaqPage(string $html, array $content): string
    {
        $faq = $content['faq'] ?? [];
        $cta = $content['cta_banner'] ?? [];
        $loginUrl = e(url(config('website.login_path', '/login')));
        $pageTitle = trim($faq['subtitle'] ?? '') ?: 'FAQ';

        $html = preg_replace(
            '/<div class="page-content pbmit-bg-color-white faq-page">/',
            '<div class="page-content pbmit-bg-color-white faq-page lineup-faq-page">',
            $html,
            1
        ) ?? $html;

        $html = preg_replace('/<h1 class="pbmit-tbar-title">\s*.*?<\/h1>/is', '<h1 class="pbmit-tbar-title">'.e($pageTitle).'</h1>', $html, 1) ?? $html;
        $html = preg_replace('/(<span class="post-root post post-post current-item">\s*).*?(<\/span>)/is', '$1'.e($pageTitle).'$2', $html, 1) ?? $html;

        $html = preg_replace(
            '/(<div class="col-md-5 pbmit-sticky-column">.*?<h4 class="pbmit-subtitle">\s*).*?(\s*<\/h4>\s*<h2 class="pbmit-title">).*?(<\/h2>)/is',
            '$1'.e($faq['subtitle'] ?? '').'$2'.nl2br(e($faq['title'] ?? '')).'$3',
            $html,
            1
        ) ?? $html;

        $accordionHtml = View::make('website.smiliz.partials.faq-accordion', [
            'accordionId' => 'accordionExample',
            'items' => $faq['items'] ?? config('website.default_faq.items', []),
        ])->render();

        $html = preg_replace(
            '/<div class="accordion" id="accordionExample">[\s\S]*<\/div>(?=\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/section>\s*<!-- Faq End -->)/',
            rtrim($accordionHtml),
            $html,
            1
        ) ?? $html;

        $html = preg_replace('/<!-- Team Start -->.*?<!-- Team End -->/is', '', $html) ?? $html;

        $html = preg_replace(
            '/<section class="section-md pbmit-bg-color-light bottom-radius position-relative">/',
            '<section class="section-md pbmit-bg-color-light bottom-radius position-relative lineup-faq-section">',
            $html,
            1
        ) ?? $html;

        $html = preg_replace(
            '/<div class="col-md-7">/',
            '<div class="col-md-7 lineup-faq-accordion-col">',
            $html,
            1
        ) ?? $html;

        $html = str_replace(
            'about-us-one-bg pbmit-bg-color-global transform-top-sec bottom-radius',
            'about-us-one-bg pbmit-bg-color-global lineup-faq-cta bottom-radius',
            $html
        );

        $html = preg_replace(
            '/(<section class="about-us-one-bg pbmit-bg-color-global lineup-faq-cta bottom-radius">.*?<h2 class="pbmit-element-title">).*?(<\/h2>\s*<div class="pbmit-heading-desc">).*?(<\/div>)/is',
            '${1}'.e($cta['rating'] ?? '').'${2}'.e($cta['rating_label'] ?? '').'${3}',
            $html,
            1
        ) ?? $html;

        $html = preg_replace(
            '/(<section class="about-us-one-bg pbmit-bg-color-global lineup-faq-cta bottom-radius">.*?<p class="about-us-one-subtitle">).*?(<\/p>\s*<h2 class="about-us-one-title">).*?(<\/h2>)/is',
            '${1}'.e($cta['subtitle'] ?? '').'${2}'.nl2br(e($cta['title'] ?? '')).'${3}',
            $html,
            1
        ) ?? $html;

        $ctaLabel = e(filled($cta['cta_label'] ?? null) ? $cta['cta_label'] : 'Doctor Portal');
        $html = preg_replace(
            '/(<section class="about-us-one-bg pbmit-bg-color-global lineup-faq-cta bottom-radius">.*?<a href=")[^"]+(" class="pbmit-btn white">.*?<span class="pbmit-button-text">).*?(<\/span>)/is',
            '$1'.$loginUrl.'$2'.$ctaLabel.'$3',
            $html,
            1
        ) ?? $html;

        return $html;
    }

    /** @param  array<string, mixed>  $content */
    private function injectAboutPage(string $html, array $content): string
    {
        $about = $content['about'] ?? [];
        $aboutPage = $content['about_page'] ?? config('website.default_about_page', []);
        $stats = $content['stats'] ?? [];
        $cta = $content['cta_banner'] ?? [];
        $loginUrl = e(url(config('website.login_path', '/login')));
        $imageUrl = e($this->website->aboutImageUrl());
        $pageTitle = $aboutPage['page_title'] ?? 'About Us';

        $html = preg_replace('/<h1 class="pbmit-tbar-title">\s*.*?<\/h1>/is', '<h1 class="pbmit-tbar-title">'.e($pageTitle).'</h1>', $html, 1) ?? $html;
        $html = preg_replace('/(<span class="post-root post post-post current-item">\s*).*?(<\/span>)/is', '$1'.e($pageTitle).'$2', $html, 1) ?? $html;

        $html = preg_replace(
            '/(<div class="about-two-leftbox">.*?<img src=")[^"]+(" class="mask-img[^"]*" alt=")/is',
            '$1'.$imageUrl.'$2',
            $html,
            1
        ) ?? $html;

        $years = (int) ($about['years'] ?? 12);
        $yearsLabel = nl2br(e($about['years_label'] ?? 'Years of aligner expertise'));
        $html = preg_replace('/(<div class="fid-style-box">.*?data-to=")\d+(")/is', '${1}'.$years.'$2', $html, 1) ?? $html;
        $html = preg_replace(
            '/(<div class="fid-style-box">.*?<span class="pbmit-number-rotate numinate"[^>]*>)\d+(<\/span>)/is',
            '${1}'.$years.'$2',
            $html,
            1
        ) ?? $html;
        $html = preg_replace(
            '/(<div class="fid-style-box">.*?<div class="pbmit-fid-desc">\s*<div class="pbmit-heading-desc">).*?(<\/div>)/is',
            '$1'.$yearsLabel.'$2',
            $html,
            1
        ) ?? $html;

        $html = preg_replace(
            '/(<div class="about-two-rightbox">.*?<h4 class="pbmit-subtitle">).*?(<\/h4>\s*<h2 class="pbmit-title">).*?(<\/h2>\s*<div class="pbmit-heading-desc">).*?(<\/div>)/is',
            '$1'.e($about['subtitle'] ?? '').'$2'.e($about['title'] ?? '').'$3'.nl2br(e($about['body'] ?? '')).'$4',
            $html,
            1
        ) ?? $html;

        $highlights = $about['highlights'] ?? [];
        $highlightIndex = 0;
        $html = preg_replace_callback(
            '/(<div class="about-two-rightbox">.*?<div class="ihbox-style-area">.*?<div class="pbmit-ihbox-style-1">.*?<h2 class="pbmit-element-title">).*?(<\/h2>\s*<\/div>\s*<div class="pbmit-heading-desc">).*?(<\/div>)/is',
            function (array $matches) use (&$highlightIndex, $highlights) {
                if (! isset($highlights[$highlightIndex])) {
                    return $matches[0];
                }

                $highlight = $highlights[$highlightIndex++];
                $replacement = $matches[1].e($highlight['title'] ?? '').$matches[2].e($highlight['description'] ?? '').$matches[3];

                return $replacement;
            },
            $html,
            2
        ) ?? $html;

        $pills = $about['pills'] ?? [];
        $pillIndex = 0;
        $html = preg_replace_callback(
            '/(<article class="pbmit-miconheading-style-2[^"]*"[^>]*>.*?<h2 class="pbmit-element-title">\s*).*?(\s*<\/h2>\s*<div class="pbmit-heading-desc">).*?(<\/div>)/is',
            function (array $matches) use (&$pillIndex, $pills) {
                if (! isset($pills[$pillIndex])) {
                    return $matches[0];
                }

                $pill = $pills[$pillIndex++];
                $replacement = $matches[1].e($pill['title'] ?? '').$matches[2].e($pill['description'] ?? '').$matches[3];

                return $replacement;
            },
            $html,
            4
        ) ?? $html;

        $discoverLabel = e($aboutPage['discover_label'] ?? 'Doctor Portal');
        $html = preg_replace(
            '/(<div class="about-two-rightbox">.*?<a href=")[^"]+(" class="pbmit-btn pbmit-hover-global">.*?<span class="pbmit-button-text">).*?(<\/span>)/is',
            '$1'.$loginUrl.'$2'.$discoverLabel.'$3',
            $html,
            1
        ) ?? $html;

        $html = preg_replace('/<!-- Client Start -->.*?<!-- Client End -->/is', '', $html) ?? $html;

        if (empty($aboutPage['show_team'])) {
            $html = preg_replace('/<!-- Team Start -->.*?<!-- Team End -->/is', '', $html) ?? $html;
        } else {
            $html = preg_replace(
                '/(<div class="pbmit-heading-subheading text-center animation-style2">\s*<h4 class="pbmit-subtitle">).*?(<\/h4>\s*<h2 class="pbmit-title">).*?(<\/h2>)/is',
                '$1'.e($aboutPage['team_subtitle'] ?? '').'$2'.e($aboutPage['team_title'] ?? '').'$3',
                $html,
                1
            ) ?? $html;

            $html = preg_replace(
                '/<div class="pbmit-divider-wrap">.*?<div class="row process-ihbox-style-13-area">.*?<\/div>\s*<\/div>\s*<\/section>\s*<!-- Team End -->/is',
                '</div></section><!-- Team End -->',
                $html,
                1
            ) ?? $html;
        }

        if (empty($aboutPage['show_testimonials'])) {
            $html = preg_replace('/<!-- Testimonial Start -->.*?<!-- Testimonial End -->/is', '', $html) ?? $html;

            $html = str_replace(
                'about-us-one-bg pbmit-bg-color-global transform-top-sec bottom-radius',
                'about-us-one-bg pbmit-bg-color-global lineup-about-cta-standalone bottom-radius',
                $html
            );
        } else {
            if (filled($aboutPage['testimonial_subtitle'] ?? null)) {
                $html = preg_replace(
                    '/(<section class="testimonial-one-bg[^"]*">.*?<h4 class="pbmit-subtitle">).*?(<\/h4>)/is',
                    '$1'.e($aboutPage['testimonial_subtitle']).'$2',
                    $html,
                    1
                ) ?? $html;
            }

            $stat = $stats[0] ?? ['value' => '750', 'label' => 'Happy customer reviews'];
            $statValue = preg_replace('/\D/', '', (string) ($stat['value'] ?? '750')) ?: '750';
            $html = preg_replace('/(<div class="testimonial-one-rightbox[^"]*">.*?data-to=")\d+(")/is', '${1}'.$statValue.'$2', $html, 1) ?? $html;
            $html = preg_replace(
                '/(<div class="testimonial-one-rightbox[^"]*">.*?<span class="pbmit-number-rotate numinate"[^>]*>)\d+(<\/span>)/is',
                '${1}'.$statValue.'$2',
                $html,
                1
            ) ?? $html;
            $html = preg_replace(
                '/(<div class="testimonial-one-rightbox[^"]*">.*?<h3 class="pbmit-fid-title">).*?(<\/h3>)/is',
                '$1'.nl2br(e($stat['label'] ?? '')).'$2',
                $html,
                1
            ) ?? $html;
        }

        $html = preg_replace(
            '/(<section class="about-us-one-bg pbmit-bg-color-global transform-top-sec bottom-radius">.*?<h2 class="pbmit-element-title">).*?(<\/h2>\s*<div class="pbmit-heading-desc">).*?(<\/div>)/is',
            '${1}'.e($cta['rating'] ?? '').'${2}'.e($cta['rating_label'] ?? '').'${3}',
            $html,
            1
        ) ?? $html;

        $html = preg_replace(
            '/(<section class="about-us-one-bg pbmit-bg-color-global transform-top-sec bottom-radius">.*?<p class="about-us-one-subtitle">).*?(<\/p>\s*<h2 class="about-us-one-title">).*?(<\/h2>)/is',
            '${1}'.e($cta['subtitle'] ?? '').'${2}'.nl2br(e($cta['title'] ?? '')).'${3}',
            $html,
            1
        ) ?? $html;

        $ctaLabel = e($cta['cta_label'] ?? 'Doctor Portal');
        $html = preg_replace(
            '/(<section class="about-us-one-bg pbmit-bg-color-global transform-top-sec bottom-radius">.*?<a href=")[^"]+(" class="pbmit-btn white">.*?<span class="pbmit-button-text">).*?(<\/span>)/is',
            '${1}'.$loginUrl.'${2}'.$ctaLabel.'${3}',
            $html,
            1
        ) ?? $html;

        return $html;
    }

    /** @param  array<string, mixed>  $content */
    private function injectContactPage(string $html, array $content): string
    {
        $contact = $content['contact'] ?? [];
        $page = $contact['page'] ?? config('website.default_contact_page', []);
        $address = filled($contact['address'] ?? null)
            ? (string) $contact['address']
            : config('website.default_contact_address', 'Amman, Jordan');
        $mapUrl = e($this->website->mapEmbedUrl($address));
        $mapLabel = e($address);

        $html = preg_replace('/<!-- Client Start -->.*?<!-- Client End -->/is', '', $html) ?? $html;

        $html = preg_replace(
            '/<iframe[^>]*src=["\']https:\/\/maps\.google\.com\/maps[^"\']*["\'][^>]*><\/iframe>/i',
            '<iframe src="'.$mapUrl.'" title="'.$mapLabel.'" aria-label="'.$mapLabel.'"></iframe>',
            $html
        ) ?? $html;

        $html = preg_replace(
            '/(<div class="contact-us-ihbox">.*?<div class="pbmit-heading-subheading">\s*)<h4 class="pbmit-subtitle">.*?<\/h4>\s*<h2 class="pbmit-title">.*?<\/h2>\s*<div class="pbmit-heading-desc">.*?<\/div>/is',
            '$1<h4 class="pbmit-subtitle">'.e($page['subtitle'] ?? '').'</h4><h2 class="pbmit-title">'.e($page['title'] ?? '').'</h2><div class="pbmit-heading-desc">'.e($page['intro'] ?? '').'</div>',
            $html,
            1
        ) ?? $html;

        $emailBody = e($contact['email'] ?? '');
        $phoneBody = e($contact['phone'] ?? '');
        if (filled($contact['hours'] ?? null)) {
            $phoneBody .= '<br>'.e($contact['hours']);
        }
        $locationBody = e($address);
        $cardTitles = [
            e($page['email_title'] ?? 'Mail us 24/7'),
            e($page['phone_title'] ?? 'Call us 24/7'),
            e($page['location_title'] ?? 'Our location'),
        ];
        $cardBodies = [$emailBody, $phoneBody, $locationBody];
        $cardIndex = 0;

        $html = preg_replace_callback(
            '/<article class="pbmit-miconheading-style-7[^"]*"[^>]*>.*?<h2 class="pbmit-element-title">\s*.*?\s*<\/h2>\s*<div class="pbmit-heading-desc">.*?<\/div>/is',
            function (array $matches) use (&$cardIndex, $cardTitles, $cardBodies) {
                if ($cardIndex > 2) {
                    return $matches[0];
                }

                $replacement = preg_replace(
                    '/<h2 class="pbmit-element-title">\s*.*?\s*<\/h2>\s*<div class="pbmit-heading-desc">.*?<\/div>/is',
                    '<h2 class="pbmit-element-title">'.$cardTitles[$cardIndex].'</h2><div class="pbmit-heading-desc">'.$cardBodies[$cardIndex].'</div>',
                    $matches[0],
                    1
                );
                $cardIndex++;

                return $replacement ?? $matches[0];
            },
            $html
        ) ?? $html;

        $html = preg_replace(
            '/(<div class="pbmit-heading-subheading text-center">\s*)<h2[^>]*class="pbmit-title"[^>]*>.*?<\/h2>\s*<div class="pbmit-heading-desc">.*?<\/div>/is',
            '$1<h2 class="pbmit-title">'.e($page['form_title'] ?? config('website.default_contact_page.form_title', 'Send us a message')).'</h2><div class="pbmit-heading-desc">'.e($page['form_intro'] ?? config('website.default_contact_page.form_intro', '')).'</div>',
            $html,
            1
        ) ?? $html;

        return $this->injectContactForm($html);
    }

    private function injectContactForm(string $html): string
    {
        $loader = '<span class="form-btn-loader d-none">'
            .'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 100"><circle fill="#fff" stroke="#fff" stroke-width="15" r="15" cx="40" cy="50"><animate attributeName="opacity" calcMode="spline" dur="2" values="1;0;1;" keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="-.4"></animate></circle><circle fill="#fff" stroke="#fff" stroke-width="15" r="15" cx="100" cy="50"><animate attributeName="opacity" calcMode="spline" dur="2" values="1;0;1;" keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="-.2"></animate></circle><circle fill="#fff" stroke="#fff" stroke-width="15" r="15" cx="160" cy="50"><animate attributeName="opacity" calcMode="spline" dur="2" values="1;0;1;" keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="0"></animate></circle></svg>'
            .'</span>';

        $formBody = '<div class="row">'
            .'<div class="col-md-6"><input type="text" class="form-control" placeholder="Name *" name="name" required></div>'
            .'<div class="col-md-6"><input type="text" class="form-control" placeholder="Phone *" name="phone" required></div>'
            .'<div class="col-md-12"><input type="email" class="form-control" placeholder="Email *" name="email" required></div>'
            .'<div class="col-md-12"><textarea name="message" cols="40" rows="8" class="form-control" placeholder="Message *" required></textarea></div>'
            .'</div>'
            .'<button class="pbmit-btn submit pbmit-form-btn" type="submit">'
            .'<span class="pbmit-icon-hover"></span>'
            .'<span class="pbmit-button-content-wrapper"><span class="pbmit-button-text">Submit</span></span>'
            .$loader
            .'</button>'
            .'<div class="message-status mt-3"></div>';

        return preg_replace(
            '/(<form\b[^>]*(?:contact-form|id=["\']contact-form["\'])[^>]*>).*?(<\/form>)/is',
            '$1'.$formBody.'$2',
            $html,
            1
        ) ?? $html;
    }

    /** @param  array<string, mixed>  $content
     * @param  array<string, mixed>  $context */
    private function injectServicePage(string $html, array $content, array $context = []): string
    {
        $page = $context['service_page']
            ?? $content['service_page']
            ?? config('website.default_service_page', []);
        $contact = $content['contact'] ?? [];
        $defaults = config('website.default_service_page', []);
        $imageUrl = e($this->website->pageImageUrl($page['image'] ?? null, $defaults['image'] ?? 'images/service/service-single-01.jpg'));
        $phone = filled($contact['phone'] ?? null) ? preg_replace('/\s+/', '', (string) $contact['phone']) : '+0(123)456-789';

        $html = preg_replace('/<h3 class="pbmit-tbar-subtitle">\s*.*?<\/h3>/is', '<h3 class="pbmit-tbar-subtitle">'.e($page['eyebrow'] ?? '').'</h3>', $html, 1) ?? $html;
        $html = preg_replace('/<h1 class="pbmit-tbar-title">\s*.*?<\/h1>/is', '<h1 class="pbmit-tbar-title">'.e($page['title'] ?? '').'</h1>', $html, 1) ?? $html;
        $html = preg_replace('/(<span class="post-root post post-post current-item">\s*).*?(<\/span>)/is', '$1'.e($page['title'] ?? '').'$2', $html, 1) ?? $html;

        $html = preg_replace(
            '/(<div class="pbmit-service-feature-image">\s*<img[^>]*src=["\']).*?(["\'])/is',
            '$1'.$imageUrl.'$2',
            $html,
            1
        ) ?? $html;

        $html = preg_replace(
            '/(<div class="pbmit-entry-content">\s*<div class="pbmit-service-feature-image">.*?<\/div>\s*<div class="pbmit-custom-title">\s*<h3 class="pbmit-title">).*?(<\/h3>)/is',
            '$1'.e($page['title'] ?? '').'$2',
            $html,
            1
        ) ?? $html;

        $paragraphIndex = 0;
        $paragraphs = array_values(array_filter([
            $page['intro'] ?? '',
            $page['body'] ?? '',
            $page['section2_body'] ?? '',
            $page['section3_body'] ?? '',
        ], fn ($value) => filled($value)));

        $html = preg_replace_callback(
            '/(<div class="pbmit-entry-content">.*?<div class="pbmit-custom-title">\s*<h3 class="pbmit-title">.*?<\/h3>\s*<\/div>\s*)<p>.*?<\/p>/is',
            function (array $matches) use (&$paragraphIndex, $paragraphs) {
                if (! isset($paragraphs[$paragraphIndex])) {
                    return $matches[0];
                }

                $replacement = $matches[1].'<p>'.e($paragraphs[$paragraphIndex]).'</p>';
                $paragraphIndex++;

                return $replacement;
            },
            $html
        ) ?? $html;

        $html = preg_replace_callback(
            '/(<div class="pbmit-entry-content">.*?<div class="pbmit-custom-title">\s*<h3 class="pbmit-title">.*?<\/h3>\s*<\/div>\s*<p>.*?<\/p>\s*)<p>.*?<\/p>/is',
            function (array $matches) use (&$paragraphIndex, $paragraphs) {
                if (! isset($paragraphs[$paragraphIndex])) {
                    return $matches[0];
                }

                $replacement = $matches[1].'<p>'.e($paragraphs[$paragraphIndex]).'</p>';
                $paragraphIndex++;

                return $replacement;
            },
            $html,
            1
        ) ?? $html;

        if (filled($page['section2_title'] ?? null)) {
            $html = preg_replace(
                '/(<div class="pbmit-entry-content">.*?<div class="pbmit-custom-title">\s*<h3 class="pbmit-title">).*?(<\/h3>\s*<\/div>\s*<p>)/is',
                '$1'.e($page['section2_title']).'$2',
                $html,
                1
            ) ?? $html;
        }

        if (filled($page['section3_title'] ?? null)) {
            $html = preg_replace(
                '/(<div data-aos="fade-up"[^>]*>\s*<div class="pbmit-custom-title">\s*<h3 class="pbmit-title">).*?(<\/h3>)/is',
                '$1'.e($page['section3_title']).'$2',
                $html,
                1
            ) ?? $html;
        }

        if (filled($page['section3_body'] ?? null)) {
            $html = preg_replace(
                '/(<div data-aos="fade-up"[^>]*>\s*<div class="pbmit-custom-title">.*?<\/div>\s*)<p>.*?<\/p>/is',
                '$1<p>'.e($page['section3_body']).'</p>',
                $html,
                1
            ) ?? $html;
        }

        if (filled($page['sidebar_heading'] ?? null)) {
            $html = preg_replace('/<h4 class="pbmit-ads-heading">.*?<\/h4>/is', '<h4 class="pbmit-ads-heading">'.e($page['sidebar_heading']).'</h4>', $html, 1) ?? $html;
        }

        if (filled($page['sidebar_text'] ?? null)) {
            $html = preg_replace('/<span class="pbmit-ads-decs">.*?<\/span>/is', '<span class="pbmit-ads-decs">'.e($page['sidebar_text']).'</span>', $html, 1) ?? $html;
        }

        if (filled($contact['phone'] ?? null)) {
            $html = preg_replace('/(<h3 class="pbmit-ads-call">\s*<a href=")tel:[^"]+(">).*?(<\/a>)/is', '$1tel:'.e($phone).'$2'.e($contact['phone']).'$3', $html, 1) ?? $html;
        }

        $services = array_values(array_filter($context['services_sidebar'] ?? $page['sidebar_services'] ?? []));
        if ($services !== []) {
            $sidebarItems = [];
            foreach ($services as $entry) {
                if (is_array($entry)) {
                    $sidebarItems[] = $entry;
                    continue;
                }

                $sidebarItems[] = ['label' => (string) $entry, 'url' => '#', 'active' => false];
            }

            $html = $this->injectServiceSidebar($html, $sidebarItems);
        }

        return $html;
    }

    /** @param  array<string, mixed>  $content
     * @param  array<string, mixed>  $context */
    private function injectBlogPage(string $html, array $content, array $context = []): string
    {
        $page = $context['blog_page']
            ?? $content['blog_page']
            ?? config('website.default_blog_page', []);
        $defaults = config('website.default_blog_page', []);
        $imageUrl = e($this->website->pageImageUrl($page['image'] ?? null, $defaults['image'] ?? 'images/blog/blog-img-01.jpg'));

        $html = preg_replace(
            '/<section class="site-content blog-details">/',
            '<section class="site-content blog-details lineup-blog-detail">',
            $html,
            1
        ) ?? $html;

        $html = preg_replace(
            '/<div class="col-md-\s+col-xl-3 blog-right-col">/',
            '<div class="col-md-12 col-lg-4 col-xl-3 blog-right-col">',
            $html,
            1
        ) ?? $html;

        $html = preg_replace(
            '/<aside class="widget widget-search">[\s\S]*?<\/aside>\s*/',
            '',
            $html,
            1
        ) ?? $html;

        $html = preg_replace(
            '/<aside class="widget widget-authorbox">[\s\S]*?<\/aside>\s*/',
            '',
            $html,
            1
        ) ?? $html;

        $html = preg_replace('/(<span class="post-root post post-post current-item">\s*).*?(<\/span>)/is', '$1'.e($page['title'] ?? '').'$2', $html, 1) ?? $html;
        $html = preg_replace(
            '/(<div class="pbmit-featured-wrapper">\s*<a[^>]*>\s*<img[^>]*src=["\']).*?(["\'])/is',
            '$1'.$imageUrl.'$2',
            $html,
            1
        ) ?? $html;

        if (filled($page['date'] ?? null)) {
            $html = preg_replace('/(<span class="entry-date">).*?(<\/span>)/is', '$1'.e($page['date']).'$2', $html, 1) ?? $html;
        }

        if (filled($page['author'] ?? null)) {
            $html = preg_replace('/(<a class="pbmit-author-link"[^>]*>).*?(<\/a>)/is', '$1'.e($page['author']).'$2', $html, 1) ?? $html;
            $html = preg_replace('/(<span class="pbmit-author-name">\s*<a[^>]*>).*?(<\/a>)/is', '$1'.e($page['author']).'$2', $html, 1) ?? $html;
        }

        if (filled($page['category'] ?? null)) {
            $html = preg_replace('/(<span class="pbmit-meta pbmit-meta-cat">.*?<a[^>]*>).*?(<\/a>)/is', '$1'.e($page['category']).'$2', $html, 1) ?? $html;
        }

        if (filled($page['intro'] ?? null)) {
            $html = preg_replace(
                '/(<div class="pbmit-entry-content">\s*)<p>\s*<span class="pbmit-drop-cap">.*?<\/span>\s*<\/p>/is',
                '$1<p>'.e($page['intro']).'</p>',
                $html,
                1
            ) ?? $html;
        }

        if (filled($page['section2_title'] ?? null)) {
            $html = preg_replace(
                '/(<div class="pbmit-entry-content">.*?<div class="pbmit-custom-title">\s*<h3 class="pbmit-title">).*?(<\/h3>)/is',
                '$1'.e($page['section2_title']).'$2',
                $html,
                1
            ) ?? $html;
        }

        if (filled($page['section2_body'] ?? null)) {
            $html = preg_replace(
                '/(<div class="pbmit-entry-content">.*?<div class="pbmit-custom-title">.*?<\/div>\s*)<p>.*?<\/p>/is',
                '$1<p>'.e($page['section2_body']).'</p>',
                $html,
                1
            ) ?? $html;
        }

        if (filled($page['section3_title'] ?? null)) {
            $html = preg_replace(
                '/(<div class="pbmit-entry-content">.*?<div class="pbmit-custom-title">\s*<h3 class="pbmit-title">).*?(<\/h3>\s*<\/div>\s*<p>.*?<\/p>\s*<div class="pbmit-block-columns)/is',
                '$1'.e($page['section3_title']).'$2',
                $html,
                1
            ) ?? $html;
        }

        if (filled($page['section3_body'] ?? null)) {
            $html = preg_replace(
                '/(<div class="pbmit-block-columns row">.*?<\/div>\s*)<p>.*?<\/p>/is',
                '$1<p>'.e($page['section3_body']).'</p>',
                $html,
                1
            ) ?? $html;
        }

        if (filled($page['quote'] ?? null)) {
            $html = preg_replace('/(<h2 class="pbmit-element-title">).*?(<\/h2>\s*<div class="pbmit-heading-desc">)/is', '$1'.e($page['quote']).'$2', $html, 1) ?? $html;
        }

        if (filled($page['quote_author'] ?? null)) {
            $html = preg_replace('/(<div class="pbmit-heading-desc">).*?(<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<div class="pbmit-custom-title">)/is', '$1'.e($page['quote_author']).'$2', $html, 1) ?? $html;
        }

        if (filled($page['author_bio'] ?? null)) {
            $html = preg_replace('/(<p class="pbmit-text pbmit-author-bio">).*?(<\/p>)/is', '$1'.e($page['author_bio']).'$2', $html, 1) ?? $html;
        }

        $tags = array_values(array_filter($page['tags'] ?? []));
        if ($tags !== []) {
            $tagHtml = '';
            foreach ($tags as $tag) {
                $tagHtml .= '<a href="#" rel="tag">'.e($tag).'</a>';
            }

            $html = preg_replace('/(<span class="pbmit-meta-tags">).*?(<\/span>)/is', '$1'.$tagHtml.'$2', $html, 1) ?? $html;
        }

        return $this->injectBlogSidebar($this->stripBlogDetailFooter($html), $content, $page);
    }

    private function stripBlogDetailFooter(string $html): string
    {
        $html = preg_replace(
            '/(<\/nav>\s*)<div class="pbmit-author-box">[\s\S]*?(?=<\/article>)/',
            '$1',
            $html,
            1
        ) ?? $html;

        $html = preg_replace(
            '/(<\/article>\s*)<div class="comments-area">[\s\S]*?(?=\s*<\/div>\s*<div class="[^"]*blog-right-col"\>)/',
            '$1',
            $html,
            1
        ) ?? $html;

        return $html;
    }

    /** @param  array<string, mixed>  $content
     * @param  array<string, mixed>  $page */
    private function injectBlogSidebar(string $html, array $content, array $page = []): string
    {
        $posts = $content['blog']['items'] ?? [];
        $categoryUrl = e($this->website->blogCategoryUrl($this->locale->current()));
        $currentSlug = $page['slug'] ?? null;
        $recentHtml = '';
        $recentCount = 0;

        foreach ($posts as $post) {
            if ($recentCount >= 3) {
                break;
            }

            if (! filled($post['title'] ?? null)) {
                continue;
            }

            if ($currentSlug && ($post['slug'] ?? null) === $currentSlug) {
                continue;
            }

            $url = e($this->website->blogPostUrl($post, $this->locale->current()));
            $imageUrl = e($this->website->blogPostImageUrl($post));
            $title = e($post['title']);
            $date = e($post['date'] ?? '');

            $recentHtml .= '<li>'
                .'<a href="'.$url.'"><span class="pbmit-rpw-img"><img src="'.$imageUrl.'" class="img-fluid" alt="'.$title.'" loading="lazy" decoding="async"></span></a>'
                .'<span class="pbmit-rpw-content">'
                .'<span class="pbmit-rpw-title"><a href="'.$url.'">'.$title.'</a></span>'
                .($date !== '' ? '<span class="pbmit-rpw-date"><a href="'.$url.'">'.$date.'</a></span>' : '')
                .'</span>'
                .'</li>';
            $recentCount++;
        }

        if ($recentHtml !== '') {
            $html = preg_replace(
                '/(<aside class="widget widget-recent-post">\s*<h2 class="widget-title">Recent Posts<\/h2>\s*<ul class="pbmit-rpw-list">\s*)([\s\S]*?)(<\/ul>\s*<\/aside>)/',
                '$1'.$recentHtml.'$3',
                $html,
                1
            ) ?? $html;
        }

        $categories = [];
        foreach ($posts as $post) {
            $category = trim((string) ($post['category'] ?? ''));
            if ($category !== '') {
                $categories[$category] = ($categories[$category] ?? 0) + 1;
            }
        }

        if ($categories !== []) {
            ksort($categories);
            $categoryHtml = '';
            foreach ($categories as $category => $count) {
                $categoryHtml .= '<li><a href="'.$categoryUrl.'">'.e($category).'</a><span class="pbmit-brackets">( '.$count.' )</span></li>';
            }

            $html = preg_replace(
                '/(<aside class="widget widget-categories">\s*<h2 class="widget-title">Category<\/h2>\s*<ul>\s*)([\s\S]*?)(<\/ul>\s*<\/aside>)/',
                '$1'.$categoryHtml.'$3',
                $html,
                1
            ) ?? $html;
        }

        $tags = [];
        foreach ($posts as $post) {
            foreach ($post['tags'] ?? [] as $tag) {
                if (filled($tag)) {
                    $tags[(string) $tag] = true;
                }
            }
        }

        foreach ($page['tags'] ?? [] as $tag) {
            if (filled($tag)) {
                $tags[(string) $tag] = true;
            }
        }

        if ($tags !== []) {
            $tagHtml = '';
            ksort($tags);
            foreach (array_keys($tags) as $tag) {
                $tagHtml .= '<li><a href="'.$categoryUrl.'" class="tag-cloud-link">'.e($tag).'</a></li>';
            }

            $html = preg_replace(
                '/(<aside class="widget widget-tag-cloud">\s*<h3 class="widget-title">Tags<\/h3>\s*<div class="tagcloud">\s*<ul class="pbmit-tag-cloud">\s*)([\s\S]*?)(<\/ul>\s*<\/div>\s*<\/aside>)/',
                '$1'.$tagHtml.'$3',
                $html,
                1
            ) ?? $html;
        }

        $cta = $content['cta_banner'] ?? config('website.default_cta_banner', []);
        $contact = $content['contact'] ?? [];

        if (filled($cta['title'] ?? null)) {
            $html = preg_replace('/<h4 class="pbmit-ads-heading">.*?<\/h4>/is', '<h4 class="pbmit-ads-heading">'.e($cta['title']).'</h4>', $html, 1) ?? $html;
        }

        $ctaText = $cta['subtitle'] ?? $cta['text'] ?? null;
        if (filled($ctaText)) {
            $html = preg_replace('/<span class="pbmit-ads-decs">.*?<\/span>/is', '<span class="pbmit-ads-decs">'.e($ctaText).'</span>', $html, 1) ?? $html;
        }

        $phone = trim((string) ($contact['phone'] ?? ''));
        if ($phone !== '') {
            $phoneHref = e(preg_replace('/\s+/', '', $phone));
            $html = preg_replace(
                '/(<h3 class="pbmit-ads-call">\s*<a href=")[^"]*(">).*?(<\/a>\s*<\/h3>)/is',
                '$1tel:'.$phoneHref.'$2'.e($phone).'$3',
                $html,
                1
            ) ?? $html;
        }

        return $html;
    }

    /** @param  array<string, mixed>  $content
     * @param  array<string, mixed>  $context */
    private function injectCaseStudyPage(string $html, array $content, array $context = []): string
    {
        $html = preg_replace(
            '/<div class="pbminfotech-gap-15px row">[\s\S]*<\/div>(?=\s*<\/div>\s*<div class="ihbox-style-12-area")/is',
            '',
            $html,
            1
        ) ?? $html;
        $html = preg_replace(
            '/<div class="ihbox-style-12-area"[\s\S]*<\/div>(?=\s*<div data-aos="fade-up" data-aos-duration="800">\s*<div class="pbmit-custom-title">\s*<h3 class="pbmit-title">\s*Our client review)/is',
            '',
            $html,
            1
        ) ?? $html;
        $html = preg_replace(
            '/<div data-aos="fade-up" data-aos-duration="800">\s*<div class="pbmit-custom-title">\s*<h3 class="pbmit-title">\s*Our client review[\s\S]*<\/div>(?=\s*<\/div>\s*<nav class="navigation post-navigation")/is',
            '',
            $html,
            1
        ) ?? $html;
        $html = preg_replace('/<div class="pbmit-portfolio-social-wrapper">[\s\S]*?<\/div>\s*/is', '', $html, 1) ?? $html;
        $html = preg_replace(
            '/<article class="pbmit-portfolio-single">/',
            '<article class="pbmit-portfolio-single lineup-case-study-detail">',
            $html,
            1
        ) ?? $html;

        $page = $context['case_study_page']
            ?? $content['case_study_page']
            ?? config('website.default_case_study_page', []);
        $defaults = config('website.default_case_study_page', []);
        $contact = $content['contact'] ?? [];
        $beforeUrl = e($this->website->pageImageUrl($page['before_image'] ?? null, $defaults['before_image'] ?? 'images/before-img-01.jpg'));
        $afterUrl = e($this->website->pageImageUrl($page['after_image'] ?? null, $defaults['after_image'] ?? 'images/after-img-01.jpg'));
        $detail1Url = e($this->website->pageImageUrl($page['detail_image1'] ?? null, $defaults['detail_image1'] ?? 'images/portfolio/portfolio-detail-01.jpg'));
        $detail2Url = e($this->website->pageImageUrl($page['detail_image2'] ?? null, $defaults['detail_image2'] ?? 'images/portfolio/portfolio-detail-02.jpg'));

        $html = preg_replace('/(<span class="post-root post post-post current-item">\s*).*?(<\/span>)/is', '$1'.e($page['title'] ?? '').'$2', $html, 1) ?? $html;
        $html = preg_replace('/<h1 class="pbmit-tbar-title">\s*.*?<\/h1>/is', '<h1 class="pbmit-tbar-title">'.e($page['title'] ?? '').'</h1>', $html, 1) ?? $html;
        $html = preg_replace('/(<img[^>]*class=["\'][^"\']*pbmit-before-image)([^"\']*)(["\'][^>]*src=["\']).*?(["\'])/is', '$1$2$3'.$beforeUrl.'$4', $html, 1) ?? $html;
        $html = preg_replace('/(<img[^>]*class=["\'][^"\']*pbmit-after-image)([^"\']*)(["\'][^>]*src=["\']).*?(["\'])/is', '$1$2$3'.$afterUrl.'$4', $html, 1) ?? $html;

        if (filled($page['summary_title'] ?? null)) {
            $html = preg_replace(
                '/(<div class="pbmit-single-project-content">.*?<div class="pbmit-custom-title">\s*<h3 class="pbmit-title">).*?(<\/h3>)/is',
                '$1'.e($page['summary_title']).'$2',
                $html,
                1
            ) ?? $html;
        }

        if (filled($page['intro'] ?? null)) {
            $html = preg_replace(
                '/(<div class="pbmit-single-project-content">.*?<div class="pbmit-entry-content">.*?<div class="pbmit-custom-title">.*?<\/div>\s*)<p>\s*<span class="pbmit-drop-cap">.*?<\/span>\s*<\/p>/is',
                '$1<p>'.e($page['intro']).'</p>',
                $html,
                1
            ) ?? $html;
        }

        if (filled($page['body'] ?? null)) {
            $html = preg_replace(
                '/(<div class="pbmit-single-project-content">.*?<div class="pbmit-entry-content">.*?<div class="pbmit-custom-title">.*?<\/div>\s*<p>.*?<\/p>\s*)<p>.*?<\/p>/is',
                '$1<p>'.e($page['body']).'</p>',
                $html,
                1
            ) ?? $html;
        }

        $defaultDetailPaths = [
            $defaults['detail_image1'] ?? 'images/portfolio/portfolio-detail-01.jpg',
            $defaults['detail_image2'] ?? 'images/portfolio/portfolio-detail-02.jpg',
        ];
        $detail1Path = $page['detail_image1'] ?? null;
        $detail2Path = $page['detail_image2'] ?? null;
        $usesStockDetailImages = in_array($detail1Path, $defaultDetailPaths, true)
            && in_array($detail2Path, $defaultDetailPaths, true);
        $detailMatchesHero = ($detail1Url === $beforeUrl && $detail2Url === $afterUrl)
            || ($detail1Url === $afterUrl && $detail2Url === $beforeUrl);

        if ($usesStockDetailImages || $detailMatchesHero) {
            $html = preg_replace(
                '/<div class="portfolio-detail-images"[\s\S]*<\/div>(?=\s*<div data-aos="fade-up"[^>]*>\s*<div class="pbmit-custom-title">\s*<h3 class="pbmit-title">\s*What we did)/is',
                '',
                $html,
                1
            ) ?? $html;
        } else {
            $detailImages = [
                $detail1Url,
                $detail2Url,
            ];
            $detailIndex = 0;
            $html = preg_replace_callback(
                '/(<div class="portfolio-detail-images"[^>]*>.*?<div class="col-md-6">\s*<div class="pbmit-animation-style7[^"]*">\s*<img[^>]*src=["\']).*?(["\'])/is',
                function (array $matches) use (&$detailIndex, $detailImages) {
                    $url = $detailImages[$detailIndex] ?? $detailImages[0];
                    $detailIndex++;

                    return $matches[1].$url.$matches[2];
                },
                $html
            ) ?? $html;
        }

        if (filled($page['what_we_did_body'] ?? null)) {
            $html = preg_replace(
                '/(<div data-aos="fade-up"[^>]*>\s*<div class="pbmit-custom-title">\s*<h3 class="pbmit-title">\s*What we did\s*<\/h3>\s*<\/div>\s*)<p>.*?<\/p>/is',
                '$1<p>'.e($page['what_we_did_body']).'</p>',
                $html,
                1
            ) ?? $html;
        }

        if (filled($page['what_we_did_title'] ?? null)) {
            $html = preg_replace(
                '/(<div data-aos="fade-up"[^>]*>\s*<div class="pbmit-custom-title">\s*<h3 class="pbmit-title">\s*)What we did(\s*<\/h3>)/is',
                '$1'.e($page['what_we_did_title']).'$2',
                $html,
                1
            ) ?? $html;
        }

        if (filled($page['sidebar_title'] ?? null)) {
            $html = preg_replace(
                '/(<div class="pbmit-single-project-details-list-inner">\s*<h3 class="pbmit-element-title">\s*).*?(<\/h3>)/is',
                '$1'.e($page['sidebar_title']).'$2',
                $html,
                1
            ) ?? $html;
        }

        if (filled($page['sidebar_intro'] ?? null)) {
            $html = preg_replace('/(<div class="pbmit-short-description">).*?(<\/div>)/is', '$1'.e($page['sidebar_intro']).'$2', $html, 1) ?? $html;
        }

        $sidebarMap = [
            'client' => 'client',
            'category' => 'category',
            'date' => 'date',
            'location' => 'location',
        ];

        foreach ($sidebarMap as $field => $class) {
            if (! filled($page[$field] ?? null)) {
                continue;
            }

            $html = preg_replace(
                '/(<li class="pbmit-portfolio-line-li '.$class.'">\s*<span class="pbmit-portfolio-line-title">.*?<\/span>\s*<span class="pbmit-portfolio-line-value">).*?(<\/span>)/is',
                '$1'.e($page[$field]).'$2',
                $html,
                1
            ) ?? $html;
        }

        $activeSlug = $context['case_study_slug'] ?? null;
        if ($activeSlug) {
            $html = $this->injectCaseStudyNavigation($html, (string) $activeSlug);
            $html = $this->injectCaseStudySidebar($html, $this->website->caseStudiesSidebarItems($this->locale->current(), $activeSlug));
        }

        $phone = filled($contact['phone'] ?? null) ? preg_replace('/\s+/', '', (string) $contact['phone']) : '';
        $phoneDisplay = filled($contact['phone'] ?? null) ? (string) $contact['phone'] : '';
        $html = preg_replace('/<h4 class="pbmit-ads-heading">.*?<\/h4>/is', '<h4 class="pbmit-ads-heading">Partner with LineUp</h4>', $html, 1) ?? $html;
        $html = preg_replace(
            '/<span class="pbmit-ads-decs">.*?<\/span>/is',
            '<span class="pbmit-ads-decs">'.e('Clear aligner manufacturing and case support for partner clinics.').'</span>',
            $html,
            1
        ) ?? $html;
        if ($phone !== '' && $phoneDisplay !== '') {
            $html = preg_replace(
                '/(<h3 class="pbmit-ads-call">\s*<a href=")tel:[^"]+(">).*?(<\/a>)/is',
                '$1tel:'.e($phone).'$2'.e($phoneDisplay).'$3',
                $html,
                1
            ) ?? $html;
        }

        return $html;
    }

    /** @param  array<int, array{label: string, url: string, active: bool}>  $sidebarItems */
    private function injectCaseStudySidebar(string $html, array $sidebarItems): string
    {
        if ($sidebarItems === []) {
            return $html;
        }

        $list = '<div class="lineup-case-study-sidebar-links"><h4 class="lineup-case-study-sidebar-links__title">More case studies</h4><ul>';
        foreach ($sidebarItems as $entry) {
            $activeClass = ! empty($entry['active']) ? ' class="is-active"' : '';
            $list .= '<li'.$activeClass.'><a href="'.e($entry['url']).'">'.e($entry['label']).'</a></li>';
        }
        $list .= '</ul></div>';

        return preg_replace('/(<aside class="widget pbmit-widget-ads">)/is', $list.'$1', $html, 1) ?? $html;
    }

    private function injectCaseStudyNavigation(string $html, string $activeSlug): string
    {
        $cases = $this->website->publishedCaseStudies();
        $index = null;

        foreach ($cases as $i => $case) {
            if (($case['slug'] ?? '') === $activeSlug) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            return $html;
        }

        $prev = $cases[$index - 1] ?? null;
        $next = $cases[$index + 1] ?? null;
        $nav = '';

        if ($prev) {
            $prevUrl = e($this->website->caseStudyDetailUrl($prev, $this->locale->current()));
            $prevTitle = e($prev['title'] ?? '');
            $nav .= '<div class="nav-previous"><a href="'.$prevUrl.'" rel="prev">'
                .'<span class="pbmit-post-nav-icon"><i class="pbmit-base-icon-right-arrow"></i><span class="pbmit-post-nav-head">Prev case</span></span>'
                .'<span class="pbmit-post-nav-wrapper"><span class="pbmit-post-nav nav-title">'.$prevTitle.'</span></span>'
                .'</a></div>';
        }

        if ($next) {
            $nextUrl = e($this->website->caseStudyDetailUrl($next, $this->locale->current()));
            $nextTitle = e($next['title'] ?? '');
            $nav .= '<div class="nav-next"><a href="'.$nextUrl.'" rel="next">'
                .'<span class="pbmit-post-nav-wrapper"><span class="pbmit-post-nav nav-title">'.$nextTitle.'</span></span>'
                .'<span class="pbmit-post-nav-icon"><span class="pbmit-post-nav-head">Next case</span><i class="pbmit-base-icon-right-arrow"></i></span>'
                .'</a></div>';
        }

        if ($prev || $next) {
            $html = preg_replace(
                '/(<nav class="navigation post-navigation"[^>]*>\s*<div class="nav-links">).*?(<\/div>\s*<\/nav>)/is',
                '$1'.$nav.'$2',
                $html,
                1
            ) ?? $html;
        }

        return $html;
    }

    /** @param  array<string, mixed>  $content */
    private function injectServicesListing(string $html, array $content): string
    {
        $platform = $content['platform'] ?? [];
        $features = $content['features'] ?? [];
        $subtitle = e($platform['subtitle'] ?? 'Services');
        $title = e($platform['title'] ?? __('website.our_services'));

        $html = preg_replace('/<h3 class="pbmit-tbar-subtitle">\s*.*?<\/h3>/is', '<h3 class="pbmit-tbar-subtitle">'.$subtitle.'</h3>', $html, 1) ?? $html;
        $html = preg_replace('/<h1 class="pbmit-tbar-title">\s*.*?<\/h1>/is', '<h1 class="pbmit-tbar-title">'.$title.'</h1>', $html, 1) ?? $html;
        $html = preg_replace('/(<span class="post-root post post-post current-item">\s*).*?(<\/span>)/is', '$1'.$title.'$2', $html, 1) ?? $html;

        $cards = '';
        foreach ($features as $index => $feature) {
            $cards .= $this->buildServiceListingCard($feature, $index);
        }

        if ($cards === '') {
            $cards = '<p>No items published yet.</p>';
        }

        $html = preg_replace(
            '/(<div class="col-md-9 service-left-col">\s*<div class="pbmit-entry-content">).*?(<\/div>\s*<\/div>\s*<div class="col-md-3 service-right-col)/is',
            '$1<div class="row g-4 lineup-services-listing">'.$cards.'</div>$2',
            $html,
            1
        ) ?? $html;

        return $this->injectServiceSidebar($html, $this->website->servicesSidebarItems($this->locale->current()));
    }

    /** @param  array<string, mixed>  $feature */
    private function buildServiceListingCard(array $feature, int $index): string
    {
        $url = e($this->website->serviceLinkUrl($feature));
        $imageUrl = e($this->website->featureImageUrl($feature, $index));
        $title = e($feature['title'] ?? '');
        $description = e($feature['description'] ?? '');
        $button = e($this->website->serviceButtonLabel($feature, __('website.read_more')));

        return '<article class="pbmit-service-style-1 col-md-6 col-lg-4">'
            .'<div class="pbminfotech-post-item h-100">'
            .'<div class="pbmit-box-content-wrap h-100">'
            .'<div class="pbmit-content-box">'
            .'<h3 class="pbmit-service-title"><a href="'.$url.'">'.$title.'</a></h3>'
            .'<div class="pbmit-service-description"><p>'.$description.'</p></div>'
            .'<a href="'.$url.'" class="pbmit-btn outline mt-3"><span class="pbmit-button-content-wrapper"><span class="pbmit-button-text">'.$button.'</span></span></a>'
            .'</div>'
            .'<div class="pbmit-service-image-wrapper"><div class="pbmit-service-image-inner">'
            .'<div class="pbmit-featured-img-wrapper"><div class="pbmit-featured-wrapper">'
            .'<a href="'.$url.'"><img src="'.$imageUrl.'" class="img-fluid" alt="'.$title.'" loading="lazy" decoding="async"></a>'
            .'</div></div></div></div>'
            .'</div></div></article>';
    }

    /** @param  array<string, mixed>  $content */
    private function injectBlogListing(string $html, array $content): string
    {
        $posts = $content['blog']['items'] ?? [];
        $page = $this->registry->resolvedSettings()['blog-classic'] ?? $this->registry->find('blog-classic') ?? [];
        $pageTitle = e($page['nav_label'] ?? $page['label'] ?? 'Blog');
        $categoryUrl = e($this->website->blogCategoryUrl($this->locale->current()));
        $articles = '';

        foreach ($posts as $index => $post) {
            $articles .= $this->buildBlogGridArticle($post, $categoryUrl);
        }

        $html = preg_replace('/<h1 class="pbmit-tbar-title">\s*.*?<\/h1>/is', '<h1 class="pbmit-tbar-title">'.$pageTitle.'</h1>', $html, 1) ?? $html;
        $html = preg_replace('/(<span class="post-root post post-post current-item">\s*).*?(<\/span>)/is', '$1'.$pageTitle.'$2', $html, 1) ?? $html;

        if ($articles !== '') {
            $html = preg_replace(
                '/(<div class="row pbmit-element-posts-wrapper">\s*)(?:<article class="pbmit-blog-style-1[^"]*"[^>]*>.*?<\/article>\s*)+/is',
                '$1'.$articles,
                $html,
                1
            ) ?? $html;
        }

        $html = preg_replace(
            '/<section class="section-lg blog-grid-col-4">/',
            '<section class="section-lg blog-grid-col-4 lineup-blog-listing">',
            $html,
            1
        ) ?? $html;

        return $html;
    }

    /** @param  array<string, mixed>  $post */
    private function buildBlogGridArticle(array $post, string $categoryUrl): string
    {
        $url = e($this->website->blogPostUrl($post, $this->locale->current()));
        $imageUrl = e($this->website->blogPostImageUrl($post));
        $title = e($post['title'] ?? '');
        $excerpt = e($post['excerpt'] ?? '');
        $date = e($post['date'] ?? '');
        $category = e($post['category'] ?? '');
        $readMore = e(__('website.read_more'));

        $metaCategory = $category !== ''
            ? '<div class="pbmit-meta-category-wrapper pbmit-meta-line"><span class="pbmit-meta-category">'
                .'<a href="'.$categoryUrl.'" rel="category tag">'.$category.'</a></span></div>'
            : '';
        $metaDate = $date !== ''
            ? '<div class="pbmit-meta-date-wrapper pbmit-meta-line"><span class="pbmit-post-date">'.$date.'</span></div>'
            : '';
        $excerptHtml = $excerpt !== ''
            ? '<div class="pbminfotech-box-desc">'.$excerpt.'</div>'
            : '';

        return '<article class="pbmit-blog-style-1 col-md-6 col-lg-4">'
            .'<div class="post-item"><div class="pbminfotech-box-content">'
            .'<div class="pbmit-featured-container"><div class="pbmit-featured-container-inner">'
            .'<div class="pbmit-featured-img-wrapper"><div class="pbmit-featured-wrapper">'
            .'<a href="'.$url.'"><img src="'.$imageUrl.'" class="img-fluid" alt="'.$title.'" loading="lazy" decoding="async"></a>'
            .'</div></div></div>'
            .'<div class="pbmit-meta-wraper-inner"><div class="pbmit-meta-wraper">'.$metaCategory.$metaDate.'</div></div>'
            .'</div>'
            .'<div class="pbmit-content-wrapper">'
            .'<h3 class="pbmit-post-title"><a href="'.$url.'">'.$title.'</a></h3>'
            .$excerptHtml
            .'<div class="pbmit-btn-hover pbmit-blog-btn">'
            .'<a class="pbmit-button-inner pbmit-button" href="'.$url.'" title="'.$title.'">'
            .'<span class="pbmit-icon-hover"></span>'
            .'<span class="pbmit-button-content-wrapper"><span class="pbmit-button-text">'.$readMore.'</span></span>'
            .'</a></div></div>'
            .'</div></div></article>';
    }

    /** @param  array<string, mixed>  $content */
    private function injectCaseStudyListing(string $html, array $content): string
    {
        $cases = $this->website->publishedCaseStudies();
        $listing = $content['case_studies'] ?? [];
        $subtitle = e($listing['subtitle'] ?? 'Case studies');
        $title = e($listing['title'] ?? 'Clinical outcomes from partner clinics');
        $articles = '';

        foreach ($cases as $index => $case) {
            $articles .= $this->buildCaseStudyListingCard($case, $index);
        }

        $html = preg_replace('/<h3 class="pbmit-tbar-subtitle">\s*.*?<\/h3>/is', '<h3 class="pbmit-tbar-subtitle">'.$subtitle.'</h3>', $html, 1) ?? $html;
        $html = preg_replace('/<h1 class="pbmit-tbar-title">\s*.*?<\/h1>/is', '<h1 class="pbmit-tbar-title">'.$title.'</h1>', $html, 1) ?? $html;
        $html = preg_replace('/(<span class="post-root post post-post current-item">\s*).*?(<\/span>)/is', '$1'.$title.'$2', $html, 1) ?? $html;

        if ($articles !== '') {
            $html = preg_replace(
                '/(<div class="row pbmit-element-posts-wrapper">\s*)(?:<article class="pbmit-portfolio-style-1[^"]*">.*?<\/article>\s*)+/is',
                '$1'.$articles,
                $html,
                1
            ) ?? $html;
        }

        return $html;
    }

    /** @param  array<string, mixed>  $case */
    private function buildCaseStudyListingCard(array $case, int $index): string
    {
        $url = e($this->website->caseStudyDetailUrl($case, $this->locale->current()));
        $title = e($case['title'] ?? '');
        $category = e($case['category'] ?? '');
        $imageUrl = e($case['after_url'] ?? $case['before_url'] ?? $this->website->smilizAsset('images/portfolio/portfolio-01b.jpg'));

        return '<article class="pbmit-portfolio-style-1 col-md-6 col-lg-4 col-xl-3">'
            .'<div class="pbminfotech-post-content"><div class="pbminfotech-image-wraper">'
            .'<div class="pbmit-featured-img-wrapper"><div class="pbmit-featured-wrapper">'
            .'<img src="'.$imageUrl.'" class="img-fluid" alt="'.$title.'" loading="lazy" decoding="async">'
            .'</div></div><a class="pbmit-link" href="'.$url.'" title="'.$title.'"></a></div>'
            .'<div class="pbminfotech-box-content"><div class="pbmit-portfolio-btn-wrapper">'
            .'<a class="pbmit-portfolio-btn" href="'.$url.'" title="'.$title.'"><span class="pbmit-button-icon"><i class="pbmit-base-icon-next-1"></i></span></a>'
            .'</div><div class="pbminfotech-box-content-inner">'
            .($category !== '' ? '<div class="pbmit-port-cat"><span>'.$category.'</span></div>' : '')
            .'<h3 class="pbmit-portfolio-title"><a href="'.$url.'">'.$title.'</a></h3>'
            .'</div></div></div></article>';
    }

    /** @param  array<int, array{label: string, url: string, active: bool}>  $sidebarItems */
    private function injectServiceSidebar(string $html, array $sidebarItems): string
    {
        if ($sidebarItems === []) {
            return $html;
        }

        $list = '';
        foreach ($sidebarItems as $entry) {
            $activeClass = ! empty($entry['active']) ? ' class="post-active"' : '';
            $list .= '<li'.$activeClass.'><a href="'.e($entry['url']).'">'.e($entry['label']).'</a></li>';
        }

        return preg_replace('/(<div class="all-post-list">\s*<ul>).*?(<\/ul>)/is', '$1'.$list.'$2', $html, 1) ?? $html;
    }

    private function wireInquiryForms(string $html, string $pageKey): string
    {
        $inquiryUrl = e(route($this->locale->routeName('website.inquiry.store')));
        $formType = str_starts_with($pageKey, 'appointment') ? 'appointment' : 'contact';

        $html = preg_replace(
            '/\baction=["\']send\.php["\']/i',
            'action="'.$inquiryUrl.'" data-lineup-inquiry-form="1" data-lineup-form-type="'.$formType.'"',
            $html
        ) ?? $html;

        $token = csrf_token();
        $honeypot = '<div class="lineup-honeypot" aria-hidden="true"><input type="text" name="website_hp" tabindex="-1" autocomplete="off" value=""></div>';

        return preg_replace_callback(
            '/(<form\b[^>]*(?:contact-form|id=["\']contact-form["\'])[^>]*>)/i',
            function (array $m) use ($token, $honeypot) {
                $opening = $m[1];

                if (! str_contains($opening, '_token')) {
                    $opening .= '<input type="hidden" name="_token" value="'.$token.'">';
                }

                if (! str_contains($opening, 'website_hp')) {
                    $opening .= $honeypot;
                }

                return $opening;
            },
            $html
        ) ?? $html;
    }

    private function applyLocaleTranslations(string $html, string $pageKey): string
    {
        if ($this->locale->current() !== 'ar') {
            return $html;
        }

        $config = config('smiliz-pages-i18n-ar', []);
        $replacements = [];

        foreach ($config['global_replacements'] ?? [] as $from => $to) {
            $replacements[$from] = $to;
        }

        foreach ($config['pages'][$pageKey] ?? [] as $from => $to) {
            $replacements[$from] = $to;
        }

        uksort($replacements, fn (string $a, string $b) => strlen($b) <=> strlen($a));

        return str_replace(array_keys($replacements), array_values($replacements), $html);
    }

    private function localizedPageTitle(string $pageKey, string $fallback): string
    {
        if ($this->locale->current() !== 'ar') {
            return $fallback;
        }

        $titles = config('smiliz-pages-i18n-ar.page_titles', []);

        return $titles[$pageKey] ?? $fallback;
    }
}
