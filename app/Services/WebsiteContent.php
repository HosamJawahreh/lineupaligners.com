<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\WebsiteShowcase;
use App\Support\PublicStorageUrl;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebsiteContent
{
    public function all(?string $locale = null): array
    {
        $locale = $this->resolveLocale($locale);
        $content = $this->baseContent();

        if ($locale !== 'en') {
            $content = $this->applyLocaleOverlay($content, $locale);
        }

        return $content;
    }

    public function allForAdmin(string $locale): array
    {
        $locale = $this->resolveLocale($locale);
        $content = $this->baseContent();

        if ($locale === 'en') {
            return $content;
        }

        return $this->applyLocaleOverlay($content, $locale, useEnglishStructure: true);
    }

    /** @return array<string, array<string, mixed>> */
    public function enabledLocales(): array
    {
        $enabled = $this->decodeJson(Setting::get('website_enabled_locales'), array_keys(config('website-locales.locales', [])));

        return collect(config('website-locales.locales', []))
            ->only($enabled)
            ->all();
    }

    private function baseContent(): array
    {
        $stored = Setting::allSettings();

        $template = (string) ($stored['website_template'] ?? config('website.default_template', 'smiliz-homepage-1'));
        $templates = config('website.templates', []);

        if (! isset($templates[$template])) {
            $template = config('website.default_template', 'smiliz-homepage-1');
        }

        $features = $this->hydrateFeatureItems(
            $this->decodeJson($stored['website_features'] ?? null, config('website.default_features')),
            $stored
        );
        $stats = $this->decodeJson($stored['website_stats'] ?? null, config('website.default_stats'));

        return [
            'published' => Setting::getBool('website_published'),
            'template' => $template,
            'template_meta' => $templates[$template] ?? [],
            'login_url' => url(config('website.login_path', '/login')),
            'slides' => $this->heroSlides($stored),
            'hero' => [
                'type' => $this->heroType($stored),
                'eyebrow' => $stored['website_hero_eyebrow'] ?? 'Welcome dentists',
                'title' => $stored['website_hero_title'] ?? 'Clear aligner manufacturing — take advantage of our expertise',
                'subtitle' => $stored['website_hero_subtitle'] ?? 'Welcome dentists to our clear aligner manufacturing. Introduce your patients to the future of clear aligners — our cutting-edge solutions deliver exceptional results.',
                'cta_label' => $this->normalizePortalLabel($stored['website_hero_cta_label'] ?? null),
                'cta_url' => filled($stored['website_hero_cta_url'] ?? null)
                    ? (string) $stored['website_hero_cta_url']
                    : config('website.login_path', '/login'),
                'image' => $stored['website_hero_image'] ?? '',
                'video' => $stored['website_hero_video'] ?? '',
            ],
            'about' => [
                'subtitle' => $stored['website_about_subtitle'] ?? 'Your winning smile fuels our passion',
                'title' => $stored['website_about_title'] ?? 'High-quality, affordable orthodontic solutions',
                'body' => $stored['website_about_body'] ?? "Lineup Aligner is dedicated to providing high-quality and affordable orthodontic solutions.\n\nOur team prioritizes innovation and precision engineering, setting a new standard in orthodontic care. We aim to empower doctors with the tools and support needed for orthodontic excellence.\n\nOur clear aligner treatments have a high success rate, disproving industry skepticism. Doctors can trust Lineup Aligner to deliver effective and reliable clear aligner solutions.",
                'years' => (int) ($stored['website_about_years'] ?? 12),
                'years_label' => $stored['website_about_years_label'] ?? 'Years of aligner expertise',
                'image' => $stored['website_about_image'] ?? '',
                'highlights' => $this->aboutHighlights($stored),
                'pills' => $this->aboutPills($features),
            ],
            'about_page' => $this->aboutPage($stored),
            'platform' => [
                'subtitle' => $this->storedText($stored, 'website_platform_subtitle', config('website.default_platform.subtitle', 'Why LINEUP')),
                'title' => $this->storedText($stored, 'website_platform_title', config('website.default_platform.title', 'What distinguishes LINEUP from others?')),
                'intro' => $this->storedText($stored, 'website_platform_intro', config('website.default_platform.intro', '')),
                'cta_label' => $stored['website_platform_cta_label'] ?? '',
                'cta_url' => $stored['website_platform_cta_url'] ?? '',
            ],
            'features' => $features,
            'stats' => $stats,
            'stats_section' => [
                'subtitle' => $stored['website_stats_subtitle'] ?? 'By the numbers',
                'title' => $stored['website_stats_title'] ?? 'Trusted by partner clinics worldwide',
                'cta_label' => $this->normalizePortalLabel($stored['website_stats_cta_label'] ?? null),
                'cta_title' => $stored['website_stats_cta_title'] ?? 'Ready to partner with LineUp?',
            ],
            'process' => [
                'subtitle' => $this->storedText($stored, 'website_process_subtitle', config('website.default_process.subtitle', 'How it works')),
                'title' => $this->storedText($stored, 'website_process_title', config('website.default_process.title', 'Your case journey with LineUp')),
                'steps' => $this->decodeJson($stored['website_process_steps'] ?? null, config('website.default_process_steps')),
            ],
            'partner_cta' => $this->partnerCta($stored),
            'cta_banner' => $this->ctaBanner($stored),
            'faq' => [
                'subtitle' => $this->storedText($stored, 'website_faq_subtitle', config('website.default_faq.subtitle', 'FAQ')),
                'title' => $this->storedText($stored, 'website_faq_title', config('website.default_faq.title', 'Frequently asked questions')),
                'items' => $this->decodeJson($stored['website_faq_items'] ?? null, config('website.default_faq.items', [])),
            ],
            'treatments' => [
                'subtitle' => $this->storedText($stored, 'website_treatments_subtitle', config('website.default_treatments.subtitle', 'Case results')),
                'title' => $this->storedText($stored, 'website_treatments_title', config('website.default_treatments.title', 'Find out why dentists love our clear aligners')),
                'intro' => $this->storedText($stored, 'website_treatments_intro', config('website.default_treatments.intro', '')),
            ],
            'case_studies' => [
                'subtitle' => $this->storedText($stored, 'website_case_studies_subtitle', config('website.default_case_studies_listing.subtitle', 'Case studies')),
                'title' => $this->storedText($stored, 'website_case_studies_title', config('website.default_case_studies_listing.title', 'Clinical outcomes from partner clinics')),
            ],
            'treatable_cases' => [
                'subtitle' => $stored['website_treatable_subtitle'] ?? config('website.default_treatable_cases.subtitle', 'Clinical scope'),
                'title' => $stored['website_treatable_title'] ?? config('website.default_treatable_cases.title', 'Treatable cases'),
                'intro' => $stored['website_treatable_intro'] ?? config('website.default_treatable_cases.intro', ''),
                'items' => $this->decodeJson($stored['website_treatable_items'] ?? null, config('website.default_treatable_cases.items', [])),
            ],
            'blog' => [
                'subtitle' => $stored['website_blog_subtitle'] ?? config('website.default_blog.subtitle', 'Fresh News'),
                'title' => $stored['website_blog_title'] ?? config('website.default_blog.title', ''),
                'items' => $this->hydrateBlogItems(
                    $this->mergeBlogItemsWithDefaults(
                        $this->decodeJson($stored['website_blog_items'] ?? null, config('website.default_blog.items', [])),
                        config('website.default_blog.items', [])
                    ),
                    $stored
                ),
            ],
            'practice_care' => $this->practiceCare($stored),
            'contact' => [
                'tagline' => $stored['website_footer_tagline'] ?? 'Your winning smile fuels our passion. Lineup Aligner — dedicated to high-quality, affordable orthodontic solutions.',
                'email' => $stored['website_contact_email'] ?? Setting::get('clinic_email', ''),
                'phone' => $stored['website_contact_phone'] ?? Setting::get('clinic_phone', ''),
                'hours' => $stored['website_contact_hours'] ?? 'Mon – Sat 08:00 – 20:00',
                'address' => filled($stored['website_contact_address'] ?? null)
                    ? $stored['website_contact_address']
                    : (Setting::get('clinic_address') ?: config('website.default_contact_address', 'Amman, Jordan')),
                'page' => $this->contactPage($stored),
            ],
            'sections' => $this->sectionVisibility($stored),
            'seo' => [
                'meta_title' => $stored['website_meta_title'] ?? Setting::get('project_name', 'LineUp Aligners'),
                'meta_description' => $stored['website_meta_description'] ?? 'LineUp Aligners — treatment case plans, 3D scans, and manufacturing workflows for doctors and clinics.',
                'titlebar_image' => $stored['website_titlebar_image'] ?? '',
            ],
            'navigation' => $this->navigation($stored),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function contentInventory(): array
    {
        $content = $this->all();
        $nav = $content['navigation'] ?? [];
        $socialCount = collect($nav['social_links'] ?? [])->filter(fn (array $row) => filled($row['url'] ?? ''))->count();
        $footerLinks = collect($nav['footer_columns'] ?? [])->flatMap(fn (array $col) => $col['links'] ?? [])->count();

        return [
            ['section' => 'hero', 'label' => 'Homepage', 'done' => filled($content['hero']['title'] ?? '') && filled($content['hero']['subtitle'] ?? ''), 'hint' => 'Headline & background'],
            ['section' => 'about', 'label' => 'About us', 'done' => filled($content['about']['body'] ?? ''), 'hint' => 'Story & photo'],
            ['section' => 'why-lineup', 'label' => 'Why LINEUP', 'done' => filled($content['platform']['title'] ?? '') && count($content['features'] ?? []) >= 3, 'hint' => 'Section heading & cards'],
            ['section' => 'how-it-works', 'label' => 'How it works', 'done' => count($content['process']['steps'] ?? []) >= 2, 'hint' => 'Workflow steps'],
            ['section' => 'services', 'label' => 'Services', 'done' => count($content['features'] ?? []) >= 1, 'hint' => 'Detail pages'],
            ['section' => 'stats', 'label' => 'Stats', 'done' => count($content['stats'] ?? []) >= 2, 'hint' => 'Trust numbers'],
            ['section' => 'portfolio', 'label' => 'Case results', 'done' => filled($content['treatments']['title'] ?? '') && ! $this->portfolioUsesDemoImages(), 'hint' => 'Carousel headings & cases'],
            ['section' => 'case-studies', 'label' => 'Case studies', 'done' => ! $this->portfolioUsesDemoImages(), 'hint' => 'Listing & detail pages'],
            ['section' => 'faq', 'label' => 'FAQ', 'done' => count($content['faq']['items'] ?? []) >= 1, 'hint' => 'Questions & answers'],
            ['section' => 'blog', 'label' => 'Blog', 'done' => count($content['blog']['items'] ?? []) >= 1, 'hint' => 'Homepage blog cards'],
            ['section' => 'partner', 'label' => 'Partner CTA', 'done' => filled($content['partner_cta']['title'] ?? ''), 'hint' => 'Doctor partnership panel'],
            ['section' => 'cta-banner', 'label' => 'CTA banner', 'done' => filled($content['cta_banner']['title'] ?? ''), 'hint' => 'Bottom homepage banner'],
            ['section' => 'contact', 'label' => 'Contact', 'done' => filled($content['contact']['email'] ?? '') || filled($content['contact']['phone'] ?? ''), 'hint' => 'Contact page, phone, email, SEO'],
            ['section' => 'navigation', 'label' => 'Footer', 'done' => $footerLinks >= 3 && $socialCount >= 1, 'hint' => 'Menus & social'],
        ];
    }

    public function resolveNavLink(array $link, ?string $locale = null): string
    {
        $locale = $this->resolveLocale($locale);
        $type = $link['type'] ?? 'url';
        $pages = app(SmilizPageRegistry::class);
        $websiteLocale = app(WebsiteLocale::class);

        return match ($type) {
            'page' => filled($link['page_key'] ?? '') && $pages->isEnabled((string) $link['page_key'])
                ? $pages->pageUrl((string) $link['page_key'], $locale)
                : (filled($link['url'] ?? '') ? (string) $link['url'] : '#'),
            'anchor' => (string) ($link['url'] ?? '#'),
            'home' => $websiteLocale->homeUrl($locale).(string) ($link['url'] ?? ''),
            default => filled($link['url'] ?? '') ? (string) $link['url'] : '#',
        };
    }

    public function utilityLinkUrl(string $source, array $contact, ?string $locale = null): string
    {
        return match ($source) {
            'phone' => filled($contact['phone'] ?? '') ? 'tel:'.preg_replace('/\s+/', '', (string) $contact['phone']) : '#',
            'email' => filled($contact['email'] ?? '') ? 'mailto:'.$contact['email'] : '#',
            'chat' => filled($contact['email'] ?? '') ? 'mailto:'.$contact['email'] : app(WebsiteLocale::class)->homeUrl($locale),
            'address' => filled($contact['address'] ?? '')
                ? 'https://www.google.com/maps/search/?api=1&query='.urlencode((string) $contact['address'])
                : '#',
            default => '#',
        };
    }

    public function mapEmbedUrl(?string $address = null): string
    {
        $query = urlencode(filled($address) ? trim((string) $address) : config('website.default_contact_address', 'Amman, Jordan'));

        return 'https://maps.google.com/maps?q='.$query.'&t=m&z=12&output=embed&iwloc=near';
    }

    public function readiness(): array
    {
        $content = $this->all();
        $showcases = WebsiteShowcase::published()->count();
        $checks = [
            ['key' => 'branding', 'label' => 'Logo & project name (Settings → Branding)', 'done' => filled(Setting::get('project_name'))],
            ['key' => 'slides', 'label' => 'Hero slides', 'done' => count($content['slides']) >= 1],
            ['key' => 'hero', 'label' => 'Hero headline & description', 'done' => filled($content['hero']['title']) && filled($content['hero']['subtitle'])],
            ['key' => 'about', 'label' => 'About us', 'done' => filled($content['about']['body'])],
            ['key' => 'features', 'label' => 'Services', 'done' => count($content['features']) >= 3],
            ['key' => 'showcases', 'label' => 'Published case studies', 'done' => $showcases >= 1],
            ['key' => 'contact', 'label' => 'Contact details', 'done' => filled($content['contact']['email']) || filled($content['contact']['phone'])],
        ];
        $done = collect($checks)->where('done', true)->count();

        return [
            'checks' => $checks,
            'percent' => (int) round(($done / max(count($checks), 1)) * 100),
            'showcase_count' => $showcases,
        ];
    }

    public function saveContent(array $data, string $editLocale = 'en'): void
    {
        $editLocale = $this->resolveLocale($editLocale);
        $slides = $this->normalizeSlides($data['slides'] ?? []);

        $this->saveStructuralSettings($data, $slides);
        $this->saveHeroMedia($data);
        $this->saveAboutImage($data);
        $this->saveTitleBarImage($data);

        if ($editLocale === 'en') {
            $treatableItems = $this->normalizeTreatableItems($data['treatable_items'] ?? []);
            $treatableItems = $this->saveTreatableImages($data['treatable_items'] ?? [], $treatableItems);
        } else {
            $existingTreatable = $this->decodeJson(Setting::get('website_treatable_items'), config('website.default_treatable_cases.items', []));
            $resizedTreatable = $this->resizeStoredList($existingTreatable, count($data['treatable_items'] ?? []), config('website.default_treatable_cases.items', []));
            $treatableItems = $this->saveTreatableImages($data['treatable_items'] ?? [], $resizedTreatable);
        }
        Setting::set('website_treatable_items', json_encode($treatableItems, JSON_UNESCAPED_UNICODE));

        if ($editLocale === 'en') {
            $blogItems = $this->normalizeBlogPosts($data['blog_posts'] ?? []);
            $blogItems = $this->saveBlogImages($data['blog_posts'] ?? [], $blogItems);
        } else {
            $existingBlog = $this->decodeJson(Setting::get('website_blog_items'), config('website.default_blog.items', []));
            $resizedBlog = $this->resizeStoredList($existingBlog, count($data['blog_posts'] ?? []), config('website.default_blog.items', []));
            $blogItems = $this->saveBlogImages($data['blog_posts'] ?? [], $resizedBlog);
        }
        Setting::set('website_blog_items', json_encode($blogItems, JSON_UNESCAPED_UNICODE));

        if ($editLocale === 'en') {
            $practiceCareItems = $this->normalizePracticeCareItems($data['practice_care_items'] ?? []);
            $practiceCareCta = $this->normalizePracticeCareCta($data);
        } else {
            $existingPracticeCare = $this->decodeJson(Setting::get('website_practice_care_items'), config('website.default_practice_care.items', []));
            $practiceCareItems = $this->mergePracticeCareSharedFields($data['practice_care_items'] ?? [], $existingPracticeCare);
            $existingCta = $this->decodeJson(Setting::get('website_practice_care_cta'), config('website.default_practice_care.cta', []));
            $practiceCareCta = array_merge($existingCta, array_filter([
                'smiliz_icon' => trim($data['practice_care_cta_icon'] ?? ''),
                'button_url' => trim($data['practice_care_cta_url'] ?? ''),
            ], fn ($value) => $value !== ''));
        }
        Setting::set('website_practice_care_items', json_encode($practiceCareItems, JSON_UNESCAPED_UNICODE));
        Setting::set('website_practice_care_cta', json_encode($practiceCareCta, JSON_UNESCAPED_UNICODE));

        if ($editLocale === 'en') {
            $processSteps = $this->normalizeProcessSteps($data['process_steps'] ?? []);
            $processSteps = $this->saveProcessStepImages($data['process_steps'] ?? [], $processSteps);
        } else {
            $existingProcess = $this->decodeJson(Setting::get('website_process_steps'), config('website.default_process_steps', []));
            $resizedProcess = $this->resizeStoredList($existingProcess, count($data['process_steps'] ?? []), config('website.default_process_steps', []));
            $processSteps = $this->saveProcessStepImages($data['process_steps'] ?? [], $resizedProcess);
        }
        Setting::set('website_process_steps', json_encode($processSteps, JSON_UNESCAPED_UNICODE));

        $this->saveSlideImages($data['slides'] ?? [], $slides, preserveText: $editLocale !== 'en');

        if (isset($data['navigation']) && is_array($data['navigation'])) {
            if ($editLocale === 'en') {
                Setting::set('website_navigation', json_encode($this->normalizeNavigation($data['navigation']), JSON_UNESCAPED_UNICODE));
            }
        }

        if ($editLocale === 'en') {
            $this->saveEnglishText($data, $slides);
        } else {
            $this->saveTranslationOverlay($editLocale, $data);
            $this->syncEnglishListCounts($data);
        }

        $this->mergeFeatureImages($data['features'] ?? []);
    }

    public function saveEnabledLocales(array $locales): void
    {
        $allowed = array_keys(config('website-locales.locales', []));
        $enabled = array_values(array_intersect($allowed, $locales));

        if ($enabled === [] || ! in_array('en', $enabled, true)) {
            $enabled = ['en', 'ar'];
        }

        Setting::set('website_enabled_locales', json_encode($enabled, JSON_UNESCAPED_UNICODE));
    }

    private function saveStructuralSettings(array $data, array $slides): void
    {
        $template = (string) ($data['website_template'] ?? config('website.default_template', 'smiliz-homepage-1'));
        $templates = config('website.templates', []);

        if (! isset($templates[$template]) || empty($templates[$template]['available'])) {
            $template = config('website.default_template', 'smiliz-homepage-1');
        }

        Setting::setMany([
            'website_published' => ! empty($data['published']),
            'website_template' => $template,
            'website_hero_type' => $this->normalizeHeroType($data['hero_type'] ?? null),
            'website_hero_cta_url' => trim($data['hero_cta_url'] ?? Setting::get('website_hero_cta_url', config('website.login_path', '/login'))),
            'website_about_years' => (string) ($data['about_years'] ?? Setting::get('website_about_years', 12)),
            'website_section_visibility' => json_encode($this->normalizeSections($data['sections'] ?? []), JSON_UNESCAPED_UNICODE),
            'website_contact_email' => $data['contact_email'] ?? Setting::get('website_contact_email', ''),
            'website_contact_phone' => $data['contact_phone'] ?? Setting::get('website_contact_phone', ''),
            'website_contact_address' => $data['contact_address'] ?? Setting::get('website_contact_address', ''),
            'website_platform_cta_url' => $data['platform_cta_url'] ?? Setting::get('website_platform_cta_url', ''),
        ]);
    }

    private function saveEnglishText(array $data, array $slides): void
    {
        $features = $this->normalizeFeatures($data['features'] ?? []);
        $stats = $this->normalizeStats($data['stats'] ?? []);
        $faqItems = $this->normalizeFaqItems($data['faq_items'] ?? []);

        Setting::setMany([
            'website_hero_slides' => json_encode($slides, JSON_UNESCAPED_UNICODE),
            'website_hero_eyebrow' => $data['hero_eyebrow'] ?? '',
            'website_hero_title' => $data['hero_title'] ?? '',
            'website_hero_subtitle' => $data['hero_subtitle'] ?? '',
            'website_hero_cta_label' => $data['hero_cta_label'] ?? '',
            'website_about_subtitle' => $data['about_subtitle'] ?? '',
            'website_about_title' => $data['about_title'] ?? '',
            'website_about_body' => $data['about_body'] ?? '',
            'website_about_years_label' => $data['about_years_label'] ?? '',
            'website_about_highlights' => json_encode($this->normalizeAboutHighlights($data['about_highlights'] ?? []), JSON_UNESCAPED_UNICODE),
            'website_platform_subtitle' => $data['platform_subtitle'] ?? '',
            'website_platform_title' => $data['platform_title'] ?? '',
            'website_platform_intro' => $data['platform_intro'] ?? '',
            'website_platform_cta_label' => $data['platform_cta_label'] ?? '',
            'website_features' => json_encode($features, JSON_UNESCAPED_UNICODE),
            'website_stats' => json_encode($stats, JSON_UNESCAPED_UNICODE),
            'website_stats_subtitle' => $data['stats_subtitle'] ?? '',
            'website_stats_title' => $data['stats_title'] ?? '',
            'website_stats_cta_label' => $data['stats_cta_label'] ?? '',
            'website_stats_cta_title' => $data['stats_cta_title'] ?? '',
            'website_process_subtitle' => $data['process_subtitle'] ?? '',
            'website_process_title' => $data['process_title'] ?? '',
            'website_faq_subtitle' => $data['faq_subtitle'] ?? '',
            'website_faq_title' => $data['faq_title'] ?? '',
            'website_faq_items' => json_encode($faqItems, JSON_UNESCAPED_UNICODE),
            'website_partner_cta' => json_encode($this->normalizePartnerCta($data), JSON_UNESCAPED_UNICODE),
            'website_cta_banner' => json_encode($this->normalizeCtaBanner($data), JSON_UNESCAPED_UNICODE),
            'website_treatments_subtitle' => $data['treatments_subtitle'] ?? '',
            'website_treatments_title' => $data['treatments_title'] ?? '',
            'website_treatments_intro' => $data['treatments_intro'] ?? '',
            'website_case_studies_subtitle' => $data['case_studies_subtitle'] ?? '',
            'website_case_studies_title' => $data['case_studies_title'] ?? '',
            'website_treatable_subtitle' => $data['treatable_subtitle'] ?? '',
            'website_treatable_title' => $data['treatable_title'] ?? '',
            'website_treatable_intro' => $data['treatable_intro'] ?? '',
            'website_blog_subtitle' => $data['blog_subtitle'] ?? '',
            'website_blog_title' => $data['blog_title'] ?? '',
            'website_practice_care_subtitle' => $data['practice_care_subtitle'] ?? '',
            'website_practice_care_title' => $data['practice_care_title'] ?? '',
            'website_footer_tagline' => $data['footer_tagline'] ?? '',
            'website_contact_hours' => $data['contact_hours'] ?? '',
            'website_contact_page' => json_encode($this->normalizeContactPage($data), JSON_UNESCAPED_UNICODE),
            'website_about_page' => json_encode($this->normalizeAboutPage($data), JSON_UNESCAPED_UNICODE),
            'website_meta_title' => $data['meta_title'] ?? '',
            'website_meta_description' => $data['meta_description'] ?? '',
        ]);
    }

    private function saveTranslationOverlay(string $locale, array $data): void
    {
        $overlay = $this->extractTranslationPayload($data);
        Setting::set('website_i18n_'.$locale, json_encode($overlay, JSON_UNESCAPED_UNICODE));
    }

    private function saveHeroMedia(array $data): void
    {
        if (! empty($data['remove_hero_image'])) {
            $this->deleteStoredFile(Setting::get('website_hero_image'));
            Setting::set('website_hero_image', '');
        } elseif ($data['hero_image'] ?? null instanceof UploadedFile) {
            $this->deleteStoredFile(Setting::get('website_hero_image'));
            Setting::set('website_hero_image', $data['hero_image']->store('website', 'public'));
        }

        if (! empty($data['remove_hero_video'])) {
            $this->deleteStoredFile(Setting::get('website_hero_video'));
            Setting::set('website_hero_video', '');
        } elseif ($data['hero_video'] ?? null instanceof UploadedFile) {
            $this->deleteStoredFile(Setting::get('website_hero_video'));
            Setting::set('website_hero_video', $data['hero_video']->store('website/videos', 'public'));
        }
    }

    public function heroImageUrl(): ?string
    {
        $path = Setting::get('website_hero_image');

        return PublicStorageUrl::url(is_string($path) ? $path : null);
    }

    public function heroVideoUrl(): string
    {
        $path = trim((string) Setting::get('website_hero_video'));

        if ($path === '') {
            $path = config('website.default_hero_video', 'videos/primecare-video.mp4');
        }

        if (str_starts_with($path, 'website/')) {
            $url = PublicStorageUrl::url($path);

            if ($url !== null) {
                return $url;
            }
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset('assets/'.$path);
    }

    public function slideImageUrl(array $slide): string
    {
        $image = trim($slide['image'] ?? '');

        if ($image === '') {
            return $this->smilizAsset('images/banner-slider-img/slider-01-slide1.jpg');
        }

        if (str_starts_with($image, 'website/')) {
            $url = PublicStorageUrl::url($image);

            if ($url !== null) {
                return $url;
            }
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://') || str_starts_with($image, '/')) {
            return $image;
        }

        return $this->smilizAsset($image);
    }

    public function treatableImageUrl(array $item): string
    {
        $image = trim($item['image'] ?? '');

        if ($image === '') {
            return $this->smilizAsset('images/homepage-1/service/service-01.jpg');
        }

        if (str_starts_with($image, 'website/')) {
            $url = PublicStorageUrl::url($image);

            if ($url !== null) {
                return $url;
            }
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://') || str_starts_with($image, '/')) {
            return $image;
        }

        return $this->smilizAsset($image);
    }

    public function processStepImageUrl(array $step, int $index = 0): string
    {
        $image = trim($step['image'] ?? '');
        $defaults = config('website.default_process_steps', []);
        $fallback = $defaults[$index]['image'] ?? 'assets/website/process/step-01-submit.svg';

        if ($image === '') {
            return asset($fallback);
        }

        if (str_starts_with($image, 'website/')) {
            $url = PublicStorageUrl::url($image);

            if ($url !== null) {
                return $url;
            }
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://') || str_starts_with($image, '/')) {
            return $image;
        }

        return asset(ltrim($image, '/'));
    }

    public function blogPostImageUrl(array $post): string
    {
        $image = trim($post['image'] ?? '');

        if ($image === '') {
            return $this->smilizAsset('images/homepage-2/blog/blog-01.jpg');
        }

        if (str_starts_with($image, 'website/')) {
            $url = PublicStorageUrl::url($image);

            if ($url !== null) {
                return $url;
            }
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://') || str_starts_with($image, '/')) {
            return $image;
        }

        return $this->smilizAsset($image);
    }

    public function blogPostUrl(array $post, ?string $locale = null): string
    {
        $url = trim($post['url'] ?? '');

        if ($url !== '') {
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
                return $url;
            }

            return url('/'.ltrim($url, '/'));
        }

        $slug = trim($post['slug'] ?? '');

        if ($slug !== '') {
            return app(WebsiteLocale::class)->pageUrl('blog/'.$slug, $locale);
        }

        $pages = app(SmilizPageRegistry::class);

        if ($pages->isEnabled('blog-classic')) {
            return $pages->pageUrl('blog-classic', $locale);
        }

        return '#';
    }

    /** @param  array<string, mixed>  $feature */
    public function serviceDetailUrl(array $feature, ?string $locale = null): string
    {
        $slug = trim($feature['slug'] ?? '');

        if ($slug === '') {
            return '#';
        }

        return app(WebsiteLocale::class)->pageUrl('services/'.$slug, $locale);
    }

    /** @param  array<string, mixed>  $item */
    public function caseStudyDetailUrl(array $item, ?string $locale = null): string
    {
        $slug = trim($item['slug'] ?? '');

        if ($slug === '') {
            return '#';
        }

        return app(WebsiteLocale::class)->pageUrl('case-studies/'.$slug, $locale);
    }

    /** @return array<string, mixed>|null */
    public function findServiceBySlug(string $slug, ?string $locale = null): ?array
    {
        $slug = Str::slug($slug);

        foreach ($this->all($locale)['features'] ?? [] as $feature) {
            if (($feature['slug'] ?? '') === $slug) {
                return $feature;
            }
        }

        return null;
    }

    /** @return array<string, mixed>|null */
    public function findBlogPostBySlug(string $slug, ?string $locale = null): ?array
    {
        $slug = Str::slug($slug);

        foreach ($this->all($locale)['blog']['items'] ?? [] as $post) {
            if (($post['slug'] ?? '') === $slug) {
                return $post;
            }
        }

        return null;
    }

    /** @return array<string, mixed>|null */
    public function findCaseStudyBySlug(string $slug): ?array
    {
        $slug = Str::slug($slug);

        foreach ($this->publishedCaseStudies() as $case) {
            if (($case['slug'] ?? '') === $slug) {
                return $case;
            }
        }

        return null;
    }

    /** @param  array<string, mixed>  $feature
     * @return array<string, mixed>
     */
    public function resolvedServiceDetail(array $feature, ?string $locale = null): array
    {
        $defaults = config('website.default_service_page', []);
        $detail = array_replace_recursive($defaults, is_array($feature['detail'] ?? null) ? $feature['detail'] : []);
        $detail['title'] = filled($detail['title'] ?? null) ? $detail['title'] : ($feature['title'] ?? '');
        $detail['sidebar_services'] = array_values(array_filter($detail['sidebar_services'] ?? []));

        if (! filled($detail['image'] ?? null) && filled($feature['image'] ?? null)) {
            $detail['image'] = $feature['image'];
        }

        return $detail;
    }

    /** @param  array<string, mixed>  $post
     * @return array<string, mixed>
     */
    public function resolvedBlogDetail(array $post, ?string $locale = null): array
    {
        $defaults = config('website.default_blog_page', []);
        $detail = array_replace_recursive($defaults, is_array($post['detail'] ?? null) ? $post['detail'] : []);
        $detail['title'] = filled($detail['title'] ?? null) ? $detail['title'] : ($post['title'] ?? '');
        $detail['category'] = filled($detail['category'] ?? null) ? $detail['category'] : ($post['category'] ?? '');
        $detail['date'] = filled($detail['date'] ?? null) ? $detail['date'] : ($post['date'] ?? '');
        $detail['tags'] = array_values(array_filter($detail['tags'] ?? []));

        if (! filled($detail['image'] ?? null) && filled($post['image'] ?? null)) {
            $detail['image'] = $post['image'];
        }

        return $detail;
    }

    /** @param  array<string, mixed>  $case
     * @return array<string, mixed>
     */
    public function resolvedCaseStudyDetail(array $case): array
    {
        $defaults = config('website.default_case_study_page', []);
        $savedDetail = is_array($case['detail'] ?? null) ? $case['detail'] : [];
        $detail = array_replace_recursive($defaults, $savedDetail);
        $title = trim($case['title'] ?? '');
        $summary = trim($case['summary'] ?? '');
        $category = trim($case['category'] ?? '');
        $months = $case['treatment_months'] ?? null;

        $detail['title'] = filled($savedDetail['title'] ?? null) ? $detail['title'] : $title;
        $detail['category'] = filled($savedDetail['category'] ?? null) ? $detail['category'] : $category;
        $detail['summary_title'] = filled($savedDetail['summary_title'] ?? null)
            ? $detail['summary_title']
            : ($defaults['summary_title'] ?? 'Case summary');
        $detail['sidebar_title'] = filled($savedDetail['sidebar_title'] ?? null)
            ? $detail['sidebar_title']
            : ($defaults['sidebar_title'] ?? 'Case info');

        if (! filled($savedDetail['client'] ?? null)) {
            $detail['client'] = $defaults['client'] ?? 'Partner clinic';
        }

        if (! filled($detail['before_image'] ?? null) && filled($case['before_image'] ?? null)) {
            $detail['before_image'] = $case['before_image'];
        } elseif (! filled($detail['before_image'] ?? null) && filled($case['before_url'] ?? null)) {
            $detail['before_image'] = $case['before_image'] ?? $defaults['before_image'];
        }

        if (! filled($detail['after_image'] ?? null) && filled($case['after_image'] ?? null)) {
            $detail['after_image'] = $case['after_image'];
        }

        if (! filled($savedDetail['intro'] ?? null) && filled($summary)) {
            $detail['intro'] = $summary;
        }

        if (! filled($savedDetail['body'] ?? null) && filled($title)) {
            $detail['body'] = sprintf(
                'This case documents %s with LineUp clear aligners. The treating doctor submitted digital scans and clinical photos through the LineUp dashboard, and our planning team delivered a staged treatment plan with approval checkpoints before manufacturing.',
                strtolower($title)
            );
        }

        if (! filled($savedDetail['what_we_did_body'] ?? null)) {
            if (filled($months)) {
                $detail['what_we_did_body'] = sprintf(
                    'We produced a full series of aligners over approximately %d months, with mid-treatment refinements and delivery timelines coordinated with the partner clinic.',
                    (int) $months
                );
            } elseif (filled($title)) {
                $detail['what_we_did_body'] = sprintf(
                    'We manufactured staged aligners for %s, supported refinements where needed, and coordinated delivery with the partner clinic through the LineUp workflow.',
                    strtolower($title)
                );
            }
        }

        if (! filled($savedDetail['sidebar_intro'] ?? null) && filled($title)) {
            $detail['sidebar_intro'] = sprintf(
                'A clear aligner case for %s — staging, clinical goals, and final outcomes from a LineUp partner clinic.',
                strtolower($title)
            );
        }

        if (! filled($savedDetail['date'] ?? null) && filled($months)) {
            $detail['date'] = sprintf('%d-month treatment', (int) $months);
        }

        return $detail;
    }

    /** @return array<int, array{label: string, url: string, active: bool}> */
    public function servicesSidebarItems(?string $locale = null, ?string $activeSlug = null): array
    {
        $locale = $this->resolveLocale($locale);
        $items = [];

        foreach ($this->all($locale)['features'] ?? [] as $feature) {
            $slug = trim($feature['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $items[] = [
                'label' => $feature['title'] ?? '',
                'url' => $this->serviceDetailUrl($feature, $locale),
                'active' => $activeSlug !== null && $slug === $activeSlug,
            ];
        }

        return $items;
    }

    /** @return array<int, array{label: string, url: string, active: bool}> */
    public function caseStudiesSidebarItems(?string $locale = null, ?string $activeSlug = null): array
    {
        $locale = $this->resolveLocale($locale);
        $items = [];
        $seenSlugs = [];
        $activeSlug = filled($activeSlug) ? Str::slug($activeSlug) : null;

        foreach ($this->publishedCaseStudies() as $case) {
            $slug = Str::slug(trim($case['slug'] ?? ''));
            if ($slug === '' || isset($seenSlugs[$slug])) {
                continue;
            }

            $seenSlugs[$slug] = true;
            $items[] = [
                'label' => $case['title'] ?? '',
                'url' => $this->caseStudyDetailUrl($case, $locale),
                'active' => $activeSlug !== null && $slug === $activeSlug,
            ];
        }

        return $items;
    }

    /** @return array<int, array<string, mixed>> */
    public function publishedCaseStudies(): array
    {
        $published = $this->publishedShowcases();

        if ($published->isNotEmpty()) {
            return $published->map(fn (WebsiteShowcase $showcase) => $this->mapShowcaseToCaseStudy($showcase))->all();
        }

        $usedSlugs = [];

        return collect(config('website.default_showcases', []))->map(function (array $item, int $index) use (&$usedSlugs) {
            $caseTypes = config('website.case_types', []);
            $title = $item['title'] ?? 'Case '.$index;
            $slug = $this->resolveItemSlug($item, $item, $title, $usedSlugs);
            $usedSlugs[] = $slug;

            return [
                'id' => 'demo-'.$index,
                'title' => $title,
                'slug' => $slug,
                'patient_label' => $item['patient_label'] ?? '',
                'category' => $caseTypes[$item['case_type'] ?? 'other'] ?? 'Case result',
                'summary' => $item['summary'] ?? '',
                'treatment_months' => $item['treatment_months'] ?? null,
                'before_image' => $item['before_image'] ?? '',
                'after_image' => $item['after_image'] ?? '',
                'before_url' => $this->smilizAsset($item['before_image']),
                'after_url' => $this->smilizAsset($item['after_image']),
                'detail' => $item['detail'] ?? [],
                'is_demo' => true,
            ];
        })->all();
    }

    /** @return array<string, mixed> */
    private function mapShowcaseToCaseStudy(WebsiteShowcase $showcase): array
    {
        return [
            'id' => $showcase->id,
            'title' => $showcase->title,
            'slug' => $showcase->slug ?: Str::slug($showcase->title),
            'patient_label' => $showcase->patient_label,
            'category' => $showcase->caseTypeLabel(),
            'summary' => $showcase->summary ?: $showcase->outcome,
            'treatment_months' => $showcase->treatment_months,
            'before_image' => $showcase->before_image,
            'after_image' => $showcase->after_image,
            'before_url' => $showcase->beforeImageUrl(),
            'after_url' => $showcase->afterImageUrl(),
            'detail' => is_array($showcase->detail) ? $showcase->detail : [],
            'is_demo' => false,
        ];
    }

    public function blogCategoryUrl(?string $locale = null): string
    {
        $pages = app(SmilizPageRegistry::class);

        if ($pages->isEnabled('blog-classic')) {
            return $pages->pageUrl('blog-classic', $locale);
        }

        return '#';
    }

    /** @param  array<string, mixed>  $item */
    public function practiceCareItemUrl(array $item, ?string $fallback = null): string
    {
        $url = trim($item['link_url'] ?? '');

        if ($url === '') {
            $pages = app(SmilizPageRegistry::class);

            if ($pages->isEnabled('service-details')) {
                return $pages->pageUrl('service-details');
            }

            return $fallback ?? url(config('website.login_path', '/login'));
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
            return $url;
        }

        return url('/'.ltrim($url, '/'));
    }

    /** @param  array<string, mixed>  $item */
    public function practiceCareButtonLabel(array $item): string
    {
        return filled($item['button_label'] ?? '')
            ? (string) $item['button_label']
            : __('website.view_details_more');
    }

    /** @param  array<string, mixed>  $content */
    public function practiceCareCtaUrl(array $content, ?string $fallback = null): string
    {
        $url = trim($content['practice_care']['cta']['button_url'] ?? '');

        if ($url === '') {
            $pages = app(SmilizPageRegistry::class);

            if ($pages->isEnabled('service-details')) {
                return $pages->pageUrl('service-details');
            }

            return $fallback ?? url(config('website.login_path', '/login'));
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
            return $url;
        }

        return url('/'.ltrim($url, '/'));
    }

    public function aboutImageUrl(?string $template = null): string
    {
        $path = trim((string) Setting::get('website_about_image'));

        if ($path !== '' && str_starts_with($path, 'website/')) {
            $url = PublicStorageUrl::url($path);

            if ($url !== null) {
                return $url;
            }
        }

        if ($path !== '' && ! str_starts_with($path, 'website/')) {
            return $this->smilizAsset($path);
        }

        $template ??= (string) Setting::get('website_template', config('website.default_template', 'smiliz-homepage-1'));
        $default = $template === 'smiliz-homepage-2'
            ? config('website.default_about_image_homepage_2', 'images/homepage-2/mask-img.png')
            : config('website.default_about_image', 'images/homepage-1/bg/about-bg1.png');

        return $this->smilizAsset($default);
    }

    public function hasCustomAboutImage(): bool
    {
        $path = trim((string) Setting::get('website_about_image'));

        if ($path === '') {
            return false;
        }

        if (str_starts_with($path, 'website/')) {
            return PublicStorageUrl::isPubliclyAccessible($path);
        }

        return true;
    }

    /** @param  array<string, mixed>  $feature */
    public function featureImageUrl(array $feature, int $index = 0): string
    {
        $image = trim($feature['image'] ?? '');
        $defaults = config('website.default_feature_images', []);
        $fallback = $defaults[$index] ?? $defaults[$index % max(count($defaults), 1)] ?? 'images/homepage-1/service/service-01.jpg';

        if ($image === '') {
            return $this->smilizAsset($fallback);
        }

        if (str_starts_with($image, 'website/')) {
            $url = PublicStorageUrl::url($image);

            if ($url !== null) {
                return $url;
            }
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://') || str_starts_with($image, '/')) {
            return $image;
        }

        return $this->smilizAsset($image);
    }

    /** @param  array<string, mixed>  $feature */
    public function serviceLinkUrl(array $feature, ?string $fallback = null): string
    {
        $url = trim($feature['link_url'] ?? '');

        if ($url !== '') {
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
                return $url;
            }

            return url('/'.ltrim($url, '/'));
        }

        if (filled($feature['slug'] ?? null)) {
            return $this->serviceDetailUrl($feature);
        }

        return $fallback ?? url(config('website.login_path', '/login'));
    }

    /** @param  array<string, mixed>  $feature */
    public function serviceButtonLabel(array $feature, string $fallback = 'Learn more'): string
    {
        return filled($feature['button_label'] ?? '') ? (string) $feature['button_label'] : $fallback;
    }

    /** @param  array<string, mixed>  $content */
    public function servicesCtaUrl(array $content, ?string $fallback = null): string
    {
        $url = trim($content['platform']['cta_url'] ?? '');

        if ($url === '') {
            return $fallback ?? url(config('website.login_path', '/login'));
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
            return $url;
        }

        return url('/'.ltrim($url, '/'));
    }

    public function smilizAsset(string $path): string
    {
        return asset('assets/smiliz/'.ltrim($path, '/'));
    }

    public function publishedShowcases(): Collection
    {
        return WebsiteShowcase::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /** @return array<int, array<string, mixed>> */
    public function portfolioItems(): array
    {
        return collect($this->publishedCaseStudies())->map(function (array $item) {
            return [
                'title' => $item['title'],
                'slug' => $item['slug'] ?? '',
                'url' => $this->caseStudyDetailUrl($item),
                'patient_label' => $item['patient_label'],
                'category' => $item['category'],
                'summary' => $item['summary'],
                'treatment_months' => $item['treatment_months'],
                'before_url' => $item['before_url'],
                'after_url' => $item['after_url'],
                'is_demo' => $item['is_demo'] ?? false,
            ];
        })->all();
    }

    public function portfolioUsesDemoImages(): bool
    {
        return $this->publishedShowcases()->isEmpty();
    }

    private function heroType(array $stored): string
    {
        return $this->normalizeHeroType($stored['website_hero_type'] ?? null);
    }

    private function normalizeHeroType(?string $type): string
    {
        return in_array($type, ['video', 'slider'], true)
            ? $type
            : config('website.default_hero_type', 'video');
    }

    private function heroSlides(array $stored): array
    {
        $slides = $this->decodeJson($stored['website_hero_slides'] ?? null, config('website.default_hero_slides'));

        if ($slides === []) {
            $slides = [[
                'eyebrow' => $stored['website_hero_eyebrow'] ?? 'LineUp Aligners',
                'title' => $stored['website_hero_title'] ?? 'Precision clear aligner plans for modern clinics',
                'cta_label' => $this->normalizePortalLabel($stored['website_hero_cta_label'] ?? null),
                'image' => 'images/banner-slider-img/slider-01-slide1.jpg',
            ]];
        }

        return array_map(fn (array $slide) => array_merge($slide, [
            'cta_label' => $this->normalizePortalLabel($slide['cta_label'] ?? null),
        ]), $slides);
    }

    private function aboutHighlights(array $stored): array
    {
        $saved = $this->decodeJson($stored['website_about_highlights'] ?? null, []);

        if ($saved !== []) {
            return $saved;
        }

        return config('website.default_about_highlights', [
            ['title' => 'Innovation & precision', 'description' => 'We prioritize innovation and precision engineering, setting a new standard in orthodontic care.'],
            ['title' => 'Proven success rate', 'description' => 'Our clear aligner treatments have a high success rate, disproving industry skepticism.'],
        ]);
    }

    private function aboutPills(array $features): array
    {
        $pills = array_slice(array_map(fn (array $f) => [
            'title' => $f['title'],
            'description' => $f['description'],
        ], $features), 2, 4);

        return $pills ?: [
            ['title' => 'Secure dashboard', 'description' => 'doctor & admin access'],
            ['title' => '3D scan review', 'description' => 'in-browser visualization'],
            ['title' => 'Case messaging', 'description' => 'per-case collaboration'],
            ['title' => 'Live notifications', 'description' => 'workflow alerts'],
        ];
    }

    private function partnerCta(array $stored): array
    {
        $defaults = config('website.default_partner_cta', []);
        $merged = array_merge($defaults, $this->decodeJson($stored['website_partner_cta'] ?? null, $defaults));
        $merged['cta_label'] = $this->normalizePortalLabel($merged['cta_label'] ?? null);

        return $merged;
    }

    private function ctaBanner(array $stored): array
    {
        $defaults = config('website.default_cta_banner', []);
        $merged = array_merge($defaults, $this->decodeJson($stored['website_cta_banner'] ?? null, $defaults));
        $merged['cta_label'] = $this->normalizePortalLabel($merged['cta_label'] ?? null);

        return $merged;
    }

    private function contactPage(array $stored): array
    {
        $defaults = config('website.default_contact_page', []);
        $saved = $this->decodeJson($stored['website_contact_page'] ?? null, $defaults);

        return [
            'subtitle' => trim($saved['subtitle'] ?? $defaults['subtitle'] ?? ''),
            'title' => trim($saved['title'] ?? $defaults['title'] ?? ''),
            'intro' => trim($saved['intro'] ?? $defaults['intro'] ?? ''),
            'email_title' => trim($saved['email_title'] ?? $defaults['email_title'] ?? ''),
            'phone_title' => trim($saved['phone_title'] ?? $defaults['phone_title'] ?? ''),
            'location_title' => trim($saved['location_title'] ?? $defaults['location_title'] ?? ''),
            'form_title' => trim($saved['form_title'] ?? $defaults['form_title'] ?? ''),
            'form_intro' => trim($saved['form_intro'] ?? $defaults['form_intro'] ?? ''),
        ];
    }

    /** @return array<string, mixed> */
    private function aboutPage(array $stored): array
    {
        $defaults = config('website.default_about_page', []);
        $saved = $this->decodeJson($stored['website_about_page'] ?? null, $defaults);

        return [
            'page_title' => trim($saved['page_title'] ?? $defaults['page_title'] ?? 'About Us'),
            'partner_title' => trim($saved['partner_title'] ?? $defaults['partner_title'] ?? ''),
            'team_subtitle' => trim($saved['team_subtitle'] ?? $defaults['team_subtitle'] ?? ''),
            'team_title' => trim($saved['team_title'] ?? $defaults['team_title'] ?? ''),
            'process_divider' => trim($saved['process_divider'] ?? $defaults['process_divider'] ?? ''),
            'testimonial_subtitle' => trim($saved['testimonial_subtitle'] ?? $defaults['testimonial_subtitle'] ?? ''),
            'discover_label' => $this->normalizePortalLabel($saved['discover_label'] ?? $defaults['discover_label'] ?? null),
            'show_team' => (bool) ($saved['show_team'] ?? $defaults['show_team'] ?? false),
            'show_testimonials' => (bool) ($saved['show_testimonials'] ?? $defaults['show_testimonials'] ?? false),
        ];
    }

    /** @return array<string, mixed> */
    private function normalizeAboutPage(array $data): array
    {
        $defaults = config('website.default_about_page', []);

        return [
            'page_title' => trim($data['about_page_title'] ?? $defaults['page_title'] ?? 'About Us'),
            'partner_title' => trim($data['about_page_partner_title'] ?? $defaults['partner_title'] ?? ''),
            'team_subtitle' => trim($data['about_page_team_subtitle'] ?? $defaults['team_subtitle'] ?? ''),
            'team_title' => trim($data['about_page_team_title'] ?? $defaults['team_title'] ?? ''),
            'process_divider' => trim($data['about_page_process_divider'] ?? $defaults['process_divider'] ?? ''),
            'testimonial_subtitle' => trim($data['about_page_testimonial_subtitle'] ?? $defaults['testimonial_subtitle'] ?? ''),
            'discover_label' => $this->normalizePortalLabel($data['about_page_discover_label'] ?? $defaults['discover_label'] ?? null),
            'show_team' => ! empty($data['about_page_show_team']),
            'show_testimonials' => ! empty($data['about_page_show_testimonials']),
        ];
    }

    private function normalizePortalLabel(?string $label, string $default = 'Doctor Portal'): string
    {
        $label = trim((string) ($label ?? ''));

        if ($label === '' || preg_match('/^doctor\s+login$/i', $label)) {
            return $default;
        }

        return $label;
    }

    private function sectionVisibility(array $stored): array
    {
        $defaults = config('website.default_sections', []);
        $saved = $this->decodeJson($stored['website_section_visibility'] ?? null, $defaults);

        return array_merge($defaults, $saved);
    }

    private function normalizeSlides(array $rows): array
    {
        $out = [];

        foreach ($rows as $row) {
            $title = trim($row['title'] ?? '');
            if ($title === '') {
                continue;
            }

            $out[] = [
                'eyebrow' => trim($row['eyebrow'] ?? ''),
                'title' => $title,
                'cta_label' => $this->normalizePortalLabel($row['cta_label'] ?? null),
                'image' => trim($row['image'] ?? ''),
            ];
        }

        return $out ?: config('website.default_hero_slides');
    }

    private function normalizeProcessSteps(array $rows): array
    {
        $existing = $this->decodeJson(Setting::get('website_process_steps'), config('website.default_process_steps', []));
        $out = [];

        foreach ($rows as $i => $row) {
            $title = trim($row['title'] ?? '');
            if ($title === '') {
                continue;
            }

            $out[] = [
                'title' => $title,
                'description' => trim($row['description'] ?? ''),
                'image' => trim($row['image'] ?? ($existing[$i]['image'] ?? '')),
            ];
        }

        return $out ?: config('website.default_process_steps');
    }

    /** @param  array<int, array<string, mixed>>  $inputRows
     * @param  array<int, array<string, mixed>>  $normalizedItems
     * @return array<int, array<string, mixed>>
     */
    private function saveProcessStepImages(array $inputRows, array $normalizedItems): array
    {
        $existing = $this->decodeJson(Setting::get('website_process_steps'), config('website.default_process_steps', []));
        $defaults = config('website.default_process_steps', []);
        $merged = [];

        foreach ($normalizedItems as $i => $item) {
            $row = $inputRows[$i] ?? [];
            $image = $item['image'];
            $defaultImage = $defaults[$i]['image'] ?? 'assets/website/process/step-01-submit.svg';

            if (! empty($row['remove_image'])) {
                if (str_starts_with($image, 'website/')) {
                    $this->deleteStoredFile($image);
                }
                $image = $defaultImage;
            } elseif (($row['image_file'] ?? null) instanceof UploadedFile) {
                if (str_starts_with($image, 'website/')) {
                    $this->deleteStoredFile($image);
                }
                $image = $row['image_file']->store('website/process', 'public');
            } elseif ($image === '' && isset($existing[$i]['image'])) {
                $image = $existing[$i]['image'];
            }

            $merged[] = array_merge($item, ['image' => $image]);
        }

        return $merged;
    }

    private function normalizeFaqItems(array $rows): array
    {
        $out = [];

        foreach ($rows as $row) {
            $question = trim($row['question'] ?? '');
            if ($question === '') {
                continue;
            }

            $out[] = [
                'question' => $question,
                'answer' => trim($row['answer'] ?? ''),
            ];
        }

        return $out ?: config('website.default_faq.items', []);
    }

    private function normalizePartnerCta(array $data): array
    {
        return [
            'quote' => trim($data['partner_quote'] ?? config('website.default_partner_cta.quote', '')),
            'title' => trim($data['partner_title'] ?? config('website.default_partner_cta.title', '')),
            'body' => trim($data['partner_body'] ?? config('website.default_partner_cta.body', '')),
            'cta_label' => $this->normalizePortalLabel($data['partner_cta_label'] ?? config('website.default_partner_cta.cta_label', 'Doctor Portal')),
        ];
    }

    private function normalizeContactPage(array $data): array
    {
        $defaults = config('website.default_contact_page', []);

        return [
            'subtitle' => trim($data['contact_page_subtitle'] ?? $defaults['subtitle'] ?? ''),
            'title' => trim($data['contact_page_title'] ?? $defaults['title'] ?? ''),
            'intro' => trim($data['contact_page_intro'] ?? $defaults['intro'] ?? ''),
            'email_title' => trim($data['contact_page_email_title'] ?? $defaults['email_title'] ?? ''),
            'phone_title' => trim($data['contact_page_phone_title'] ?? $defaults['phone_title'] ?? ''),
            'location_title' => trim($data['contact_page_location_title'] ?? $defaults['location_title'] ?? ''),
            'form_title' => trim($data['contact_page_form_title'] ?? $defaults['form_title'] ?? ''),
            'form_intro' => trim($data['contact_page_form_intro'] ?? $defaults['form_intro'] ?? ''),
        ];
    }

    private function normalizeCtaBanner(array $data): array
    {
        return [
            'rating' => trim($data['cta_rating'] ?? config('website.default_cta_banner.rating', '')),
            'rating_label' => trim($data['cta_rating_label'] ?? config('website.default_cta_banner.rating_label', '')),
            'subtitle' => trim($data['cta_subtitle'] ?? config('website.default_cta_banner.subtitle', '')),
            'title' => trim($data['cta_title'] ?? config('website.default_cta_banner.title', '')),
            'cta_label' => $this->normalizePortalLabel($data['cta_banner_label'] ?? config('website.default_cta_banner.cta_label', 'Doctor Portal')),
        ];
    }

    private function normalizeSections(array $sections): array
    {
        $defaults = config('website.default_sections', []);
        $out = [];

        foreach ($defaults as $key => $default) {
            $out[$key] = ! empty($sections[$key]);
        }

        return $out;
    }

    private function saveSlideImages(array $inputRows, array $normalizedSlides, bool $preserveText = false): void
    {
        $existing = $this->heroSlides(Setting::allSettings());
        $merged = [];

        foreach ($normalizedSlides as $i => $slide) {
            $row = $inputRows[$i] ?? [];
            $image = $slide['image'];
            $base = $existing[$i] ?? $slide;

            if (! empty($row['remove_image'])) {
                if (str_starts_with($image, 'website/')) {
                    $this->deleteStoredFile($image);
                }
                $image = config('website.default_hero_slides.'.$i.'.image', 'images/banner-slider-img/slider-01-slide1.jpg');
            } elseif (($row['image_file'] ?? null) instanceof UploadedFile) {
                if (str_starts_with($image, 'website/')) {
                    $this->deleteStoredFile($image);
                }
                $image = $row['image_file']->store('website/slides', 'public');
            } elseif ($image === '' && isset($existing[$i]['image'])) {
                $image = $existing[$i]['image'];
            }

            $merged[] = $preserveText
                ? array_merge($base, ['image' => $image])
                : array_merge($slide, ['image' => $image]);
        }

        Setting::set('website_hero_slides', json_encode($merged, JSON_UNESCAPED_UNICODE));
    }

    private function saveAboutImage(array $data): void
    {
        if (! empty($data['remove_about_image'])) {
            $this->deleteStoredFile(Setting::get('website_about_image'));
            Setting::set('website_about_image', '');
        } elseif (($data['about_image'] ?? null) instanceof UploadedFile) {
            $this->deleteStoredFile(Setting::get('website_about_image'));
            Setting::set('website_about_image', $data['about_image']->store('website/about', 'public'));
        }
    }

    private function saveTitleBarImage(array $data): void
    {
        if (! empty($data['remove_titlebar_image'])) {
            $this->deleteStoredFile(Setting::get('website_titlebar_image'));
            Setting::set('website_titlebar_image', '');
        } elseif (($data['titlebar_image'] ?? null) instanceof UploadedFile) {
            $this->deleteStoredFile(Setting::get('website_titlebar_image'));
            Setting::set('website_titlebar_image', $data['titlebar_image']->store('website/titlebar', 'public'));
        }
    }

    /** @param  array<int, array<string, mixed>>  $inputRows */
    private function mergeFeatureImages(array $inputRows): void
    {
        if ($inputRows === []) {
            return;
        }

        $features = $this->decodeJson(Setting::get('website_features'), config('website.default_features', []));
        $defaults = config('website.default_feature_images', []);
        $defaultFeatures = config('website.default_features', []);
        $merged = [];
        $count = max(count($features), count($inputRows));

        for ($i = 0; $i < $count; $i++) {
            $feature = $features[$i] ?? $defaultFeatures[$i] ?? [
                'icon' => 'zmdi-star',
                'title' => '',
                'description' => '',
                'image' => '',
                'button_label' => '',
                'link_url' => '',
            ];
            $row = $inputRows[$i] ?? [];
            $image = trim($feature['image'] ?? '');
            $defaultImage = $defaults[$i] ?? $defaults[$i % max(count($defaults), 1)] ?? 'images/homepage-1/service/service-01.jpg';

            if (! empty($row['remove_image'])) {
                if (str_starts_with($image, 'website/')) {
                    $this->deleteStoredFile($image);
                }
                $image = '';
            } elseif (($row['image_file'] ?? null) instanceof UploadedFile) {
                if (str_starts_with($image, 'website/')) {
                    $this->deleteStoredFile($image);
                }
                $image = $row['image_file']->store('website/features', 'public');
            } elseif ($image === '' && isset($row['image']) && trim((string) $row['image']) !== '') {
                $image = trim((string) $row['image']);
            }

            $detail = is_array($feature['detail'] ?? null) ? $feature['detail'] : [];
            $detailInput = is_array($row['detail'] ?? null) ? $row['detail'] : [];
            $detail['image'] = $this->saveNestedDetailImage(
                $detailInput,
                (string) ($detail['image'] ?? config('website.default_service_page.image', 'images/service/service-single-01.jpg')),
                'website/service'
            );

            $merged[] = array_merge($feature, [
                'image' => $image,
                'icon' => trim($row['icon'] ?? $feature['icon'] ?? 'zmdi-star'),
                'link_url' => array_key_exists('link_url', $row)
                    ? trim((string) $row['link_url'])
                    : ($feature['link_url'] ?? ''),
                'slug' => $feature['slug'] ?? ($existing[$i]['slug'] ?? ''),
                'detail' => $detail,
            ]);
        }

        Setting::set('website_features', json_encode($merged, JSON_UNESCAPED_UNICODE));
    }

    /** @param  array<int, array<string, mixed>>  $existing
     * @param  array<int, array<string, mixed>>  $fallback
     * @return array<int, array<string, mixed>>
     */
    private function resizeStoredList(array $existing, int $targetCount, array $fallback = []): array
    {
        if ($targetCount <= 0) {
            return $existing;
        }

        $out = [];

        for ($i = 0; $i < $targetCount; $i++) {
            $out[] = $existing[$i] ?? $fallback[$i] ?? ($existing[array_key_last($existing)] ?? []);
        }

        return $out;
    }

    /** Keep English list lengths in sync when editing translations (text stays in overlay). */
    private function syncEnglishListCounts(array $data): void
    {
        $features = $this->decodeJson(Setting::get('website_features'), config('website.default_features', []));
        Setting::set('website_features', json_encode(
            $this->resizeStoredList($features, count($data['features'] ?? []), config('website.default_features', [])),
            JSON_UNESCAPED_UNICODE
        ));

        $stats = $this->decodeJson(Setting::get('website_stats'), config('website.default_stats', []));
        Setting::set('website_stats', json_encode(
            $this->resizeStoredList($stats, count($data['stats'] ?? []), config('website.default_stats', [])),
            JSON_UNESCAPED_UNICODE
        ));

        $steps = $this->decodeJson(Setting::get('website_process_steps'), config('website.default_process_steps', []));
        Setting::set('website_process_steps', json_encode(
            $this->resizeStoredList($steps, count($data['process_steps'] ?? []), config('website.default_process_steps', [])),
            JSON_UNESCAPED_UNICODE
        ));

        $faq = $this->decodeJson(Setting::get('website_faq_items'), config('website.default_faq.items', []));
        Setting::set('website_faq_items', json_encode(
            $this->resizeStoredList($faq, count($data['faq_items'] ?? []), config('website.default_faq.items', [])),
            JSON_UNESCAPED_UNICODE
        ));

        $blog = $this->decodeJson(Setting::get('website_blog_items'), config('website.default_blog.items', []));
        Setting::set('website_blog_items', json_encode(
            $this->resizeStoredList($blog, count($data['blog_posts'] ?? []), config('website.default_blog.items', [])),
            JSON_UNESCAPED_UNICODE
        ));

        $practiceCare = $this->decodeJson(Setting::get('website_practice_care_items'), config('website.default_practice_care.items', []));
        Setting::set('website_practice_care_items', json_encode(
            $this->resizeStoredList($practiceCare, count($data['practice_care_items'] ?? []), config('website.default_practice_care.items', [])),
            JSON_UNESCAPED_UNICODE
        ));
    }

    /** @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function normalizeAboutHighlights(array $rows): array
    {
        $defaults = config('website.default_about_highlights', []);
        $out = [];

        foreach ($rows as $i => $row) {
            $title = trim($row['title'] ?? '');
            if ($title === '') {
                continue;
            }

            $out[] = [
                'title' => $title,
                'description' => trim($row['description'] ?? ($defaults[$i]['description'] ?? '')),
            ];
        }

        return $out ?: $defaults;
    }

    private function normalizeFeatures(array $rows): array
    {
        $existing = $this->decodeJson(Setting::get('website_features'), config('website.default_features', []));
        $out = [];
        $usedSlugs = [];

        foreach ($rows as $i => $row) {
            $title = trim($row['title'] ?? '');
            if ($title === '') {
                continue;
            }

            $slug = $this->resolveItemSlug($row, $existing[$i] ?? [], $title, $usedSlugs);
            $usedSlugs[] = $slug;

            $out[] = [
                'icon' => trim($row['icon'] ?? 'zmdi-star'),
                'title' => $title,
                'description' => trim($row['description'] ?? ''),
                'image' => trim($row['image'] ?? ($existing[$i]['image'] ?? '')),
                'button_label' => trim($row['button_label'] ?? ($existing[$i]['button_label'] ?? '')),
                'link_url' => trim($row['link_url'] ?? ($existing[$i]['link_url'] ?? '')),
                'slug' => $slug,
                'detail' => $this->normalizeServiceDetail($row, $existing[$i] ?? [], $title),
            ];
        }

        return $out ?: $this->hydrateFeatureItems(config('website.default_features'), Setting::allSettings());
    }

    private function normalizeStats(array $rows): array
    {
        $out = [];

        foreach ($rows as $row) {
            $value = trim($row['value'] ?? '');
            $label = trim($row['label'] ?? '');
            if ($value === '' || $label === '') {
                continue;
            }

            $out[] = ['value' => $value, 'label' => $label];
        }

        return $out ?: config('website.default_stats');
    }

    private function decodeJson(?string $json, array $fallback): array
    {
        if (! $json) {
            return $fallback;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) && $decoded !== [] ? $decoded : $fallback;
    }

    /** @param  array<string, mixed>  $stored */
    private function storedText(array $stored, string $key, string $default): string
    {
        $value = $stored[$key] ?? null;

        return filled($value) ? (string) $value : $default;
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path && str_starts_with($path, 'website/') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function resolveLocale(?string $locale): string
    {
        if ($locale === null || $locale === '') {
            $locale = app()->bound('website.locale')
                ? (string) app('website.locale')
                : app(WebsiteLocale::class)->current();
        }

        $locale = strtolower(trim((string) $locale));
        $locales = config('website-locales.locales', []);

        return array_key_exists($locale, $locales) ? $locale : config('website-locales.default', 'en');
    }

    /** @return array<string, mixed> */
    private function translationDefaults(string $locale): array
    {
        return match ($locale) {
            'ar' => config('website-i18n-ar', []),
            default => [],
        };
    }

    /** @return array<string, mixed> */
    private function storedTranslations(string $locale): array
    {
        $stored = $this->decodeJson(Setting::get('website_i18n_'.$locale), $this->translationDefaults($locale));

        return array_replace_recursive($this->translationDefaults($locale), $stored);
    }

    private function applyLocaleOverlay(array $content, string $locale, bool $useEnglishStructure = false): array
    {
        $overlay = $this->storedTranslations($locale);

        if ($overlay === []) {
            return $content;
        }

        foreach (['slides', 'features', 'stats'] as $listKey) {
            if (! empty($overlay[$listKey]) && is_array($overlay[$listKey])) {
                $preserve = $listKey === 'features' ? ['icon', 'smiliz_icon', 'image', 'link_url', 'slug'] : [];
                $content[$listKey] = $this->mergeIndexedList($content[$listKey] ?? [], $overlay[$listKey], $preserve);
            }
        }

        if (! empty($overlay['features'])) {
            foreach ($content['features'] ?? [] as $i => $feature) {
                $detailOverlay = $overlay['features'][$i]['detail'] ?? null;
                if (is_array($detailOverlay) && $detailOverlay !== []) {
                    $content['features'][$i]['detail'] = array_replace_recursive(
                        $content['features'][$i]['detail'] ?? [],
                        $detailOverlay
                    );
                }
            }
        }

        if (! empty($overlay['platform']) && is_array($overlay['platform'])) {
            $content['platform'] = array_replace_recursive($content['platform'] ?? [], $overlay['platform']);
        }

        foreach (['hero', 'about', 'about_page', 'stats_section', 'process', 'partner_cta', 'cta_banner', 'faq', 'blog', 'practice_care', 'treatments', 'case_studies', 'treatable_cases', 'contact', 'seo', 'navigation'] as $section) {
            if (! empty($overlay[$section]) && is_array($overlay[$section])) {
                $content[$section] = array_replace_recursive($content[$section] ?? [], $overlay[$section]);
            }
        }

        if (! empty($overlay['process']['steps'])) {
            $content['process']['steps'] = $this->mergeIndexedList(
                $content['process']['steps'] ?? [],
                $overlay['process']['steps'],
                ['image']
            );
        }

        if (! empty($overlay['faq']['items'])) {
            $content['faq']['items'] = $this->mergeIndexedList($content['faq']['items'] ?? [], $overlay['faq']['items']);
        }

        if (! empty($overlay['treatable_cases']['items'])) {
            $content['treatable_cases']['items'] = $this->mergeIndexedList(
                $content['treatable_cases']['items'] ?? [],
                $overlay['treatable_cases']['items'],
                ['image']
            );
        }

        if (! empty($overlay['blog']['items'])) {
            $content['blog']['items'] = $this->mergeIndexedList(
                $content['blog']['items'] ?? [],
                $overlay['blog']['items'],
                ['image', 'date', 'url', 'slug']
            );

            foreach ($content['blog']['items'] ?? [] as $i => $post) {
                $detailOverlay = $overlay['blog']['items'][$i]['detail'] ?? null;
                if (is_array($detailOverlay) && $detailOverlay !== []) {
                    $content['blog']['items'][$i]['detail'] = array_replace_recursive(
                        $content['blog']['items'][$i]['detail'] ?? [],
                        $detailOverlay
                    );
                }
            }
        }

        if (! empty($overlay['practice_care']['items'])) {
            $content['practice_care']['items'] = $this->mergeIndexedList(
                $content['practice_care']['items'] ?? [],
                $overlay['practice_care']['items'],
                ['smiliz_icon', 'link_url', 'button_label']
            );
        }

        if (! empty($overlay['practice_care']['cta']) && is_array($overlay['practice_care']['cta'])) {
            $content['practice_care']['cta'] = array_replace_recursive(
                $content['practice_care']['cta'] ?? [],
                array_filter($overlay['practice_care']['cta'], fn ($value) => $value !== null && $value !== '')
            );
        }

        if (! empty($overlay['about']['highlights'])) {
            $content['about']['highlights'] = $this->mergeIndexedList($content['about']['highlights'] ?? [], $overlay['about']['highlights']);
        }

        if (! empty($overlay['navigation']['footer_columns'])) {
            foreach ($overlay['navigation']['footer_columns'] as $i => $colOverlay) {
                if (! isset($content['navigation']['footer_columns'][$i])) {
                    continue;
                }

                if (filled($colOverlay['title'] ?? null)) {
                    $content['navigation']['footer_columns'][$i]['title'] = $colOverlay['title'];
                }

                if (! empty($colOverlay['links']) && isset($content['navigation']['footer_columns'][$i]['links'])) {
                    $content['navigation']['footer_columns'][$i]['links'] = $this->mergeIndexedList(
                        $content['navigation']['footer_columns'][$i]['links'],
                        $colOverlay['links']
                    );
                }
            }
        }

        if (! empty($overlay['navigation']['services_column']['title'])) {
            $content['navigation']['services_column']['title'] = $overlay['navigation']['services_column']['title'];
        }

        if (! empty($overlay['navigation']['newsletter']) && is_array($overlay['navigation']['newsletter'])) {
            $content['navigation']['newsletter'] = array_replace_recursive(
                $content['navigation']['newsletter'] ?? [],
                array_filter($overlay['navigation']['newsletter'], fn ($value) => $value !== null && $value !== '')
            );
        }

        if (! empty($overlay['navigation']['footer_utility'])) {
            $content['navigation']['footer_utility'] = $this->mergeIndexedList(
                $content['navigation']['footer_utility'] ?? [],
                $overlay['navigation']['footer_utility']
            );
        }

        if (! empty($overlay['navigation']['bottom_links'])) {
            $content['navigation']['bottom_links'] = $this->mergeIndexedList(
                $content['navigation']['bottom_links'] ?? [],
                $overlay['navigation']['bottom_links']
            );
        }

        if ($useEnglishStructure) {
            return $content;
        }

        $content['hero']['cta_label'] = $overlay['hero']['cta_label'] ?? $content['hero']['cta_label'];
        $content['stats_section']['cta_label'] = $overlay['stats_section']['cta_label'] ?? $content['stats_section']['cta_label'];
        $content['partner_cta']['cta_label'] = $overlay['partner_cta']['cta_label'] ?? $content['partner_cta']['cta_label'];
        $content['cta_banner']['cta_label'] = $overlay['cta_banner']['cta_label'] ?? $content['cta_banner']['cta_label'];

        if (! empty($content['features'])) {
            $content['about']['pills'] = $this->aboutPills($content['features']);
        }

        return $content;
    }

    /** @param  array<int, array<string, mixed>>  $base
     * @param  array<int, array<string, mixed>>  $overlay
     * @param  array<int, string>  $preserveKeys
     * @return array<int, array<string, mixed>>
     */
    private function mergeIndexedList(array $base, array $overlay, array $preserveKeys = []): array
    {
        $merged = [];

        foreach ($base as $i => $row) {
            $patch = $overlay[$i] ?? [];
            $item = array_merge($row, $patch);

            foreach ($preserveKeys as $key) {
                if (isset($row[$key])) {
                    $item[$key] = $row[$key];
                }
            }

            $merged[] = $item;
        }

        for ($i = count($base); $i < count($overlay); $i++) {
            if (! empty($overlay[$i]) && is_array($overlay[$i])) {
                $merged[] = $overlay[$i];
            }
        }

        return $merged;
    }

    /** @return array<string, mixed> */
    private function extractTranslationPayload(array $data): array
    {
        return [
            'slides' => collect($data['slides'] ?? [])->map(fn (array $slide) => [
                'eyebrow' => trim($slide['eyebrow'] ?? ''),
                'title' => trim($slide['title'] ?? ''),
                'cta_label' => trim($slide['cta_label'] ?? ''),
            ])->values()->all(),
            'hero' => [
                'eyebrow' => trim($data['hero_eyebrow'] ?? ''),
                'title' => trim($data['hero_title'] ?? ''),
                'subtitle' => trim($data['hero_subtitle'] ?? ''),
                'cta_label' => trim($data['hero_cta_label'] ?? ''),
            ],
            'about' => [
                'subtitle' => trim($data['about_subtitle'] ?? ''),
                'title' => trim($data['about_title'] ?? ''),
                'body' => trim($data['about_body'] ?? ''),
                'years_label' => trim($data['about_years_label'] ?? ''),
                'highlights' => collect($data['about_highlights'] ?? [])->map(fn (array $row) => [
                    'title' => trim($row['title'] ?? ''),
                    'description' => trim($row['description'] ?? ''),
                ])->values()->all(),
            ],
            'about_page' => [
                'page_title' => trim($data['about_page_title'] ?? ''),
                'partner_title' => trim($data['about_page_partner_title'] ?? ''),
                'team_subtitle' => trim($data['about_page_team_subtitle'] ?? ''),
                'team_title' => trim($data['about_page_team_title'] ?? ''),
                'process_divider' => trim($data['about_page_process_divider'] ?? ''),
                'testimonial_subtitle' => trim($data['about_page_testimonial_subtitle'] ?? ''),
                'discover_label' => trim($data['about_page_discover_label'] ?? ''),
            ],
            'platform' => [
                'subtitle' => trim($data['platform_subtitle'] ?? ''),
                'title' => trim($data['platform_title'] ?? ''),
                'intro' => trim($data['platform_intro'] ?? ''),
                'cta_label' => trim($data['platform_cta_label'] ?? ''),
            ],
            'features' => collect($data['features'] ?? [])->map(fn (array $row) => [
                'title' => trim($row['title'] ?? ''),
                'description' => trim($row['description'] ?? ''),
                'button_label' => trim($row['button_label'] ?? ''),
                'detail' => $this->extractServiceDetailTranslation($row),
            ])->values()->all(),
            'stats' => collect($data['stats'] ?? [])->map(fn (array $row) => [
                'value' => trim($row['value'] ?? ''),
                'label' => trim($row['label'] ?? ''),
            ])->values()->all(),
            'stats_section' => [
                'subtitle' => trim($data['stats_subtitle'] ?? ''),
                'title' => trim($data['stats_title'] ?? ''),
                'cta_label' => trim($data['stats_cta_label'] ?? ''),
                'cta_title' => trim($data['stats_cta_title'] ?? ''),
            ],
            'process' => [
                'subtitle' => trim($data['process_subtitle'] ?? ''),
                'title' => trim($data['process_title'] ?? ''),
                'steps' => collect($data['process_steps'] ?? [])->map(fn (array $row) => [
                    'title' => trim($row['title'] ?? ''),
                    'description' => trim($row['description'] ?? ''),
                ])->values()->all(),
            ],
            'faq' => [
                'subtitle' => trim($data['faq_subtitle'] ?? ''),
                'title' => trim($data['faq_title'] ?? ''),
                'items' => collect($data['faq_items'] ?? [])->map(fn (array $row) => [
                    'question' => trim($row['question'] ?? ''),
                    'answer' => trim($row['answer'] ?? ''),
                ])->values()->all(),
            ],
            'partner_cta' => [
                'quote' => trim($data['partner_quote'] ?? ''),
                'title' => trim($data['partner_title'] ?? ''),
                'body' => trim($data['partner_body'] ?? ''),
                'cta_label' => trim($data['partner_cta_label'] ?? ''),
            ],
            'cta_banner' => [
                'rating' => trim($data['cta_rating'] ?? ''),
                'rating_label' => trim($data['cta_rating_label'] ?? ''),
                'subtitle' => trim($data['cta_subtitle'] ?? ''),
                'title' => trim($data['cta_title'] ?? ''),
                'cta_label' => trim($data['cta_banner_label'] ?? ''),
            ],
            'treatments' => [
                'subtitle' => trim($data['treatments_subtitle'] ?? ''),
                'title' => trim($data['treatments_title'] ?? ''),
                'intro' => trim($data['treatments_intro'] ?? ''),
            ],
            'case_studies' => [
                'subtitle' => trim($data['case_studies_subtitle'] ?? ''),
                'title' => trim($data['case_studies_title'] ?? ''),
            ],
            'treatable_cases' => [
                'subtitle' => trim($data['treatable_subtitle'] ?? ''),
                'title' => trim($data['treatable_title'] ?? ''),
                'intro' => trim($data['treatable_intro'] ?? ''),
                'items' => collect($data['treatable_items'] ?? [])->map(fn (array $row) => [
                    'title' => trim($row['title'] ?? ''),
                    'description' => trim($row['description'] ?? ''),
                ])->values()->all(),
            ],
            'blog' => [
                'subtitle' => trim($data['blog_subtitle'] ?? ''),
                'title' => trim($data['blog_title'] ?? ''),
                'items' => collect($data['blog_posts'] ?? [])->map(fn (array $row) => [
                    'title' => trim($row['title'] ?? ''),
                    'excerpt' => trim($row['excerpt'] ?? ''),
                    'category' => trim($row['category'] ?? ''),
                    'detail' => $this->extractBlogDetailTranslation($row),
                ])->values()->all(),
            ],
            'practice_care' => [
                'subtitle' => trim($data['practice_care_subtitle'] ?? ''),
                'title' => trim($data['practice_care_title'] ?? ''),
                'items' => collect($data['practice_care_items'] ?? [])->map(fn (array $row) => [
                    'title' => trim($row['title'] ?? ''),
                    'description' => trim($row['description'] ?? ''),
                    'button_label' => trim($row['button_label'] ?? ''),
                ])->values()->all(),
                'cta' => [
                    'title' => trim($data['practice_care_cta_title'] ?? ''),
                    'button_label' => trim($data['practice_care_cta_label'] ?? ''),
                ],
            ],
            'contact' => [
                'tagline' => trim($data['footer_tagline'] ?? ''),
                'hours' => trim($data['contact_hours'] ?? ''),
                'page' => [
                    'subtitle' => trim($data['contact_page_subtitle'] ?? ''),
                    'title' => trim($data['contact_page_title'] ?? ''),
                    'intro' => trim($data['contact_page_intro'] ?? ''),
                    'email_title' => trim($data['contact_page_email_title'] ?? ''),
                    'phone_title' => trim($data['contact_page_phone_title'] ?? ''),
                    'location_title' => trim($data['contact_page_location_title'] ?? ''),
                    'form_title' => trim($data['contact_page_form_title'] ?? ''),
                    'form_intro' => trim($data['contact_page_form_intro'] ?? ''),
                ],
            ],
            'navigation' => $this->extractNavigationTranslation($data['navigation'] ?? []),
            'seo' => [
                'meta_title' => trim($data['meta_title'] ?? ''),
                'meta_description' => trim($data['meta_description'] ?? ''),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function extractServiceDetailTranslation(array $row): array
    {
        $detail = is_array($row['detail'] ?? null) ? $row['detail'] : [];

        return [
            'eyebrow' => trim($detail['eyebrow'] ?? ''),
            'title' => trim($detail['title'] ?? ''),
            'intro' => trim($detail['intro'] ?? ''),
            'body' => trim($detail['body'] ?? ''),
            'section2_title' => trim($detail['section2_title'] ?? ''),
            'section2_body' => trim($detail['section2_body'] ?? ''),
            'section3_title' => trim($detail['section3_title'] ?? ''),
            'section3_body' => trim($detail['section3_body'] ?? ''),
            'sidebar_heading' => trim($detail['sidebar_heading'] ?? ''),
            'sidebar_text' => trim($detail['sidebar_text'] ?? ''),
            'sidebar_services' => $this->normalizeMultilineList($detail['sidebar_services_text'] ?? ($detail['sidebar_services'] ?? '')),
        ];
    }

    /** @return array<string, mixed> */
    private function extractBlogDetailTranslation(array $row): array
    {
        $detail = is_array($row['detail'] ?? null) ? $row['detail'] : [];

        return [
            'title' => trim($detail['title'] ?? ''),
            'category' => trim($detail['category'] ?? ''),
            'date' => trim($detail['date'] ?? ''),
            'author' => trim($detail['author'] ?? ''),
            'author_bio' => trim($detail['author_bio'] ?? ''),
            'intro' => trim($detail['intro'] ?? ''),
            'section2_title' => trim($detail['section2_title'] ?? ''),
            'section2_body' => trim($detail['section2_body'] ?? ''),
            'section3_title' => trim($detail['section3_title'] ?? ''),
            'section3_body' => trim($detail['section3_body'] ?? ''),
            'quote' => trim($detail['quote'] ?? ''),
            'quote_author' => trim($detail['quote_author'] ?? ''),
            'tags' => $this->normalizeTagList($detail['tags'] ?? ''),
        ];
    }

    /** @return array<string, mixed> */
    private function extractServicePageTranslation(array $data): array
    {
        return [
            'eyebrow' => trim($data['service_page_eyebrow'] ?? ''),
            'title' => trim($data['service_page_title'] ?? ''),
            'intro' => trim($data['service_page_intro'] ?? ''),
            'body' => trim($data['service_page_body'] ?? ''),
            'section2_title' => trim($data['service_page_section2_title'] ?? ''),
            'section2_body' => trim($data['service_page_section2_body'] ?? ''),
            'section3_title' => trim($data['service_page_section3_title'] ?? ''),
            'section3_body' => trim($data['service_page_section3_body'] ?? ''),
            'sidebar_heading' => trim($data['service_page_sidebar_heading'] ?? ''),
            'sidebar_text' => trim($data['service_page_sidebar_text'] ?? ''),
            'sidebar_services' => $this->normalizeMultilineList($data['service_page_sidebar_services_text'] ?? ''),
        ];
    }

    /** @return array<int, string> */
    private function normalizeMultilineList(mixed $value): array
    {
        if (is_array($value)) {
            return collect($value)->map(fn ($line) => trim((string) $line))->filter()->values()->all();
        }

        return collect(preg_split('/\r\n|\r|\n/', trim((string) $value)) ?: [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    private function extractBlogPageTranslation(array $data): array
    {
        return [
            'title' => trim($data['blog_page_title'] ?? ''),
            'category' => trim($data['blog_page_category'] ?? ''),
            'date' => trim($data['blog_page_date'] ?? ''),
            'author' => trim($data['blog_page_author'] ?? ''),
            'author_bio' => trim($data['blog_page_author_bio'] ?? ''),
            'intro' => trim($data['blog_page_intro'] ?? ''),
            'section2_title' => trim($data['blog_page_section2_title'] ?? ''),
            'section2_body' => trim($data['blog_page_section2_body'] ?? ''),
            'section3_title' => trim($data['blog_page_section3_title'] ?? ''),
            'section3_body' => trim($data['blog_page_section3_body'] ?? ''),
            'quote' => trim($data['blog_page_quote'] ?? ''),
            'quote_author' => trim($data['blog_page_quote_author'] ?? ''),
            'tags' => $this->normalizeTagList($data['blog_page_tags'] ?? ''),
        ];
    }

    /** @return array<string, mixed> */
    private function extractCaseStudyPageTranslation(array $data): array
    {
        return [
            'title' => trim($data['case_study_page_title'] ?? ''),
            'summary_title' => trim($data['case_study_page_summary_title'] ?? ''),
            'sidebar_intro' => trim($data['case_study_page_sidebar_intro'] ?? ''),
            'intro' => trim($data['case_study_page_intro'] ?? ''),
            'body' => trim($data['case_study_page_body'] ?? ''),
            'what_we_did_title' => trim($data['case_study_page_what_we_did_title'] ?? ''),
            'what_we_did_body' => trim($data['case_study_page_what_we_did_body'] ?? ''),
            'client' => trim($data['case_study_page_client'] ?? ''),
            'category' => trim($data['case_study_page_category'] ?? ''),
            'date' => trim($data['case_study_page_date'] ?? ''),
            'location' => trim($data['case_study_page_location'] ?? ''),
        ];
    }

    /** @return array<int, string> */
    private function normalizeTagList(mixed $value): array
    {
        if (is_array($value)) {
            return collect($value)->map(fn ($tag) => trim((string) $tag))->filter()->values()->all();
        }

        return collect(preg_split('/\s*,\s*/', trim((string) $value)) ?: [])
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeTreatableItems(array $rows): array
    {
        $existing = $this->decodeJson(Setting::get('website_treatable_items'), config('website.default_treatable_cases.items', []));
        $out = [];

        foreach ($rows as $i => $row) {
            $title = trim($row['title'] ?? '');
            if ($title === '') {
                continue;
            }

            $out[] = [
                'title' => $title,
                'description' => trim($row['description'] ?? ''),
                'image' => trim($row['image'] ?? ($existing[$i]['image'] ?? '')),
            ];
        }

        return $out ?: config('website.default_treatable_cases.items', []);
    }

    /** @param  array<int, array<string, mixed>>  $inputRows
     * @param  array<int, array<string, mixed>>  $normalizedItems
     * @return array<int, array<string, mixed>>
     */
    private function saveTreatableImages(array $inputRows, array $normalizedItems): array
    {
        $existing = $this->decodeJson(Setting::get('website_treatable_items'), config('website.default_treatable_cases.items', []));
        $defaults = config('website.default_treatable_cases.items', []);
        $merged = [];

        foreach ($normalizedItems as $i => $item) {
            $row = $inputRows[$i] ?? [];
            $image = $item['image'];
            $defaultImage = $defaults[$i]['image'] ?? 'images/homepage-1/service/service-01.jpg';

            if (! empty($row['remove_image'])) {
                if (str_starts_with($image, 'website/')) {
                    $this->deleteStoredFile($image);
                }
                $image = $defaultImage;
            } elseif (($row['image_file'] ?? null) instanceof UploadedFile) {
                if (str_starts_with($image, 'website/')) {
                    $this->deleteStoredFile($image);
                }
                $image = $row['image_file']->store('website/treatable', 'public');
            } elseif ($image === '' && isset($existing[$i]['image'])) {
                $image = $existing[$i]['image'];
            }

            $merged[] = array_merge($item, ['image' => $image]);
        }

        return $merged;
    }

    private function normalizeBlogPosts(array $rows): array
    {
        $existing = $this->decodeJson(Setting::get('website_blog_items'), config('website.default_blog.items', []));
        $out = [];
        $usedSlugs = [];

        foreach ($rows as $i => $row) {
            $title = trim($row['title'] ?? '');
            if ($title === '') {
                continue;
            }

            $slug = $this->resolveItemSlug($row, $existing[$i] ?? [], $title, $usedSlugs);
            $usedSlugs[] = $slug;

            $out[] = [
                'title' => $title,
                'excerpt' => trim($row['excerpt'] ?? ''),
                'category' => trim($row['category'] ?? ''),
                'date' => trim($row['date'] ?? ($existing[$i]['date'] ?? '')),
                'image' => trim($row['image'] ?? ($existing[$i]['image'] ?? '')),
                'url' => trim($row['url'] ?? ($existing[$i]['url'] ?? '')),
                'slug' => $slug,
                'detail' => $this->normalizeBlogDetail($row, $existing[$i] ?? [], $title),
            ];
        }

        return $out ?: $this->hydrateBlogItems(config('website.default_blog.items', []), Setting::allSettings());
    }

    /** @param  array<int, array<string, mixed>>  $inputRows
     * @param  array<int, array<string, mixed>>  $normalizedItems
     * @return array<int, array<string, mixed>>
     */
    private function saveBlogImages(array $inputRows, array $normalizedItems): array
    {
        $existing = $this->decodeJson(Setting::get('website_blog_items'), config('website.default_blog.items', []));
        $defaults = config('website.default_blog.items', []);
        $merged = [];

        foreach ($normalizedItems as $i => $item) {
            $row = $inputRows[$i] ?? [];
            $image = $item['image'];
            $defaultImage = $defaults[$i]['image'] ?? 'images/homepage-2/blog/blog-01.jpg';

            if (! empty($row['remove_image'])) {
                if (str_starts_with($image, 'website/')) {
                    $this->deleteStoredFile($image);
                }
                $image = $defaultImage;
            } elseif (($row['image_file'] ?? null) instanceof UploadedFile) {
                if (str_starts_with($image, 'website/')) {
                    $this->deleteStoredFile($image);
                }
                $image = $row['image_file']->store('website/blog', 'public');
            } elseif ($image === '' && isset($existing[$i]['image'])) {
                $image = $existing[$i]['image'];
            }

            $detail = is_array($item['detail'] ?? null) ? $item['detail'] : [];
            $detailInput = is_array($row['detail'] ?? null) ? $row['detail'] : [];
            $detail['image'] = $this->saveNestedDetailImage(
                $detailInput,
                (string) ($detail['image'] ?? config('website.default_blog_page.image', 'images/blog/blog-img-01.jpg')),
                'website/blog'
            );

            $merged[] = array_merge($item, [
                'image' => $image,
                'slug' => $item['slug'] ?? ($existing[$i]['slug'] ?? ''),
                'detail' => $detail,
            ]);
        }

        return $merged;
    }

    /** @return array<string, mixed> */
    private function practiceCare(array $stored): array
    {
        $defaults = config('website.default_practice_care', []);
        $cta = $this->decodeJson($stored['website_practice_care_cta'] ?? null, $defaults['cta'] ?? []);

        return [
            'subtitle' => $stored['website_practice_care_subtitle'] ?? ($defaults['subtitle'] ?? 'Practice Care'),
            'title' => $stored['website_practice_care_title'] ?? ($defaults['title'] ?? ''),
            'items' => $this->decodeJson($stored['website_practice_care_items'] ?? null, $defaults['items'] ?? []),
            'cta' => array_replace_recursive($defaults['cta'] ?? [], is_array($cta) ? $cta : []),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function normalizePracticeCareItems(array $rows): array
    {
        $existing = $this->decodeJson(Setting::get('website_practice_care_items'), config('website.default_practice_care.items', []));
        $defaults = config('website.default_practice_care.items', []);
        $out = [];

        foreach ($rows as $i => $row) {
            $title = trim($row['title'] ?? '');
            if ($title === '') {
                continue;
            }

            $out[] = [
                'smiliz_icon' => trim($row['smiliz_icon'] ?? ($existing[$i]['smiliz_icon'] ?? ($defaults[$i]['smiliz_icon'] ?? 'pbmit-smiliz-icon-dental-care'))),
                'title' => $title,
                'description' => trim($row['description'] ?? ''),
                'button_label' => trim($row['button_label'] ?? ($existing[$i]['button_label'] ?? 'View Details More')),
                'link_url' => trim($row['link_url'] ?? ($existing[$i]['link_url'] ?? '')),
            ];
        }

        return $out ?: config('website.default_practice_care.items', []);
    }

    /** @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, array<string, mixed>>  $existing
     * @return array<int, array<string, mixed>>
     */
    private function mergePracticeCareSharedFields(array $rows, array $existing): array
    {
        $merged = [];

        foreach ($existing as $i => $item) {
            $row = $rows[$i] ?? [];
            $merged[] = array_merge($item, array_filter([
                'smiliz_icon' => trim($row['smiliz_icon'] ?? ''),
                'link_url' => trim($row['link_url'] ?? ''),
            ], fn ($value) => $value !== ''));
        }

        return $this->resizeStoredList($merged, count($rows), config('website.default_practice_care.items', []));
    }

    /** @return array<string, mixed> */
    private function normalizePracticeCareCta(array $data): array
    {
        $defaults = config('website.default_practice_care.cta', []);
        $existing = $this->decodeJson(Setting::get('website_practice_care_cta'), $defaults);

        return [
            'smiliz_icon' => trim($data['practice_care_cta_icon'] ?? ($existing['smiliz_icon'] ?? ($defaults['smiliz_icon'] ?? 'pbmit-smiliz-icon-dental-chair'))),
            'title' => trim($data['practice_care_cta_title'] ?? ($existing['title'] ?? '')),
            'button_label' => trim($data['practice_care_cta_label'] ?? ($existing['button_label'] ?? '')),
            'button_url' => trim($data['practice_care_cta_url'] ?? ($existing['button_url'] ?? '')),
        ];
    }

    /** @return array<string, mixed> */
    private function navigation(array $stored): array
    {
        $defaults = config('website.default_navigation', []);
        $saved = $this->decodeJson($stored['website_navigation'] ?? null, []);

        return $this->normalizeNavigation(array_replace_recursive($defaults, $saved));
    }

    /** @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function normalizeNavigation(array $input): array
    {
        $defaults = config('website.default_navigation', []);

        $social = collect($input['social_links'] ?? $defaults['social_links'] ?? [])
            ->map(fn (array $row) => [
                'network' => trim($row['network'] ?? 'link'),
                'title' => trim($row['title'] ?? ''),
                'url' => trim($row['url'] ?? ''),
            ])->values()->all();

        $columns = collect($input['footer_columns'] ?? $defaults['footer_columns'] ?? [])
            ->map(function (array $column) {
                $links = collect($column['links'] ?? [])
                    ->map(fn (array $link) => [
                        'label' => trim($link['label'] ?? ''),
                        'type' => in_array($link['type'] ?? '', ['page', 'anchor', 'home', 'url'], true) ? $link['type'] : 'url',
                        'page_key' => trim($link['page_key'] ?? ''),
                        'url' => trim($link['url'] ?? ''),
                    ])
                    ->filter(fn (array $link) => $link['label'] !== '')
                    ->values()
                    ->all();

                return [
                    'title' => trim($column['title'] ?? ''),
                    'links' => $links,
                ];
            })
            ->filter(fn (array $column) => $column['title'] !== '' || $column['links'] !== [])
            ->values()
            ->all();

        $services = $input['services_column'] ?? $defaults['services_column'] ?? [];
        $newsletter = $input['newsletter'] ?? $defaults['newsletter'] ?? [];
        $utility = collect($input['footer_utility'] ?? $defaults['footer_utility'] ?? [])
            ->map(fn (array $row) => [
                'label' => trim($row['label'] ?? ''),
                'source' => in_array($row['source'] ?? '', ['phone', 'email', 'address', 'chat'], true) ? $row['source'] : 'phone',
                'chat_label' => trim($row['chat_label'] ?? 'Chat with us'),
            ])
            ->filter(fn (array $row) => $row['label'] !== '')
            ->values()
            ->all();

        $bottom = collect($input['bottom_links'] ?? $defaults['bottom_links'] ?? [])
            ->map(fn (array $link) => [
                'label' => trim($link['label'] ?? ''),
                'type' => in_array($link['type'] ?? '', ['page', 'anchor', 'home', 'url'], true) ? $link['type'] : 'page',
                'page_key' => trim($link['page_key'] ?? ''),
                'url' => trim($link['url'] ?? ''),
            ])
            ->filter(fn (array $link) => $link['label'] !== '')
            ->values()
            ->all();

        return [
            'social_links' => $social ?: ($defaults['social_links'] ?? []),
            'footer_columns' => $columns ?: ($defaults['footer_columns'] ?? []),
            'services_column' => [
                'enabled' => ! empty($services['enabled']),
                'title' => trim($services['title'] ?? 'Our services'),
                'use_features' => ! array_key_exists('use_features', $services) || ! empty($services['use_features']),
                'feature_limit' => max(1, min(10, (int) ($services['feature_limit'] ?? 5))),
                'feature_link' => trim($services['feature_link'] ?? '#services') ?: '#services',
            ],
            'newsletter' => [
                'enabled' => ! array_key_exists('enabled', $newsletter) || ! empty($newsletter['enabled']),
                'title' => trim($newsletter['title'] ?? ''),
                'blurb' => trim($newsletter['blurb'] ?? ''),
            ],
            'footer_utility' => $utility ?: ($defaults['footer_utility'] ?? []),
            'bottom_links' => $bottom ?: ($defaults['bottom_links'] ?? []),
        ];
    }

    /** @param  array<string, mixed>  $navigation
     * @return array<string, mixed>
     */
    private function extractNavigationTranslation(array $navigation): array
    {
        return [
            'footer_columns' => collect($navigation['footer_columns'] ?? [])
                ->map(fn (array $column) => [
                    'title' => trim($column['title'] ?? ''),
                    'links' => collect($column['links'] ?? [])
                        ->map(fn (array $link) => ['label' => trim($link['label'] ?? '')])
                        ->values()->all(),
                ])->values()->all(),
            'services_column' => [
                'title' => trim($navigation['services_column']['title'] ?? ''),
            ],
            'newsletter' => [
                'title' => trim($navigation['newsletter']['title'] ?? ''),
                'blurb' => trim($navigation['newsletter']['blurb'] ?? ''),
            ],
            'footer_utility' => collect($navigation['footer_utility'] ?? [])
                ->map(fn (array $row) => [
                    'label' => trim($row['label'] ?? ''),
                    'chat_label' => trim($row['chat_label'] ?? ''),
                ])->values()->all(),
            'bottom_links' => collect($navigation['bottom_links'] ?? [])
                ->map(fn (array $link) => ['label' => trim($link['label'] ?? '')])
                ->values()->all(),
        ];
    }

    /** @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    private function servicePage(array $stored): array
    {
        $defaults = config('website.default_service_page', []);
        $saved = $this->decodeJson($stored['website_service_page'] ?? null, $defaults);

        return $this->mergePageDefaults($defaults, $saved, ['sidebar_services']);
    }

    /** @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    private function blogPage(array $stored): array
    {
        $defaults = config('website.default_blog_page', []);
        $saved = $this->decodeJson($stored['website_blog_page'] ?? null, $defaults);

        return $this->mergePageDefaults($defaults, $saved, ['tags']);
    }

    /** @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    private function caseStudyPage(array $stored): array
    {
        $defaults = config('website.default_case_study_page', []);
        $saved = $this->decodeJson($stored['website_case_study_page'] ?? null, $defaults);

        return $this->mergePageDefaults($defaults, $saved);
    }

    /** @param  array<string, mixed>  $defaults
     * @param  array<string, mixed>  $saved
     * @param  array<int, string>  $listKeys
     * @return array<string, mixed>
     */
    private function mergePageDefaults(array $defaults, array $saved, array $listKeys = []): array
    {
        $merged = array_replace_recursive($defaults, $saved);

        foreach ($defaults as $key => $defaultValue) {
            if (in_array($key, $listKeys, true)) {
                $merged[$key] = filled($saved[$key] ?? null) ? (array) $saved[$key] : (array) $defaultValue;

                continue;
            }

            $merged[$key] = trim((string) ($saved[$key] ?? $defaultValue ?? ''));
        }

        return $merged;
    }

    /** @return array<string, mixed> */
    private function normalizeServicePage(array $data): array
    {
        return $this->extractServicePageTranslation($data);
    }

    /** @return array<string, mixed> */
    private function normalizeBlogPage(array $data): array
    {
        return $this->extractBlogPageTranslation($data);
    }

    /** @return array<string, mixed> */
    private function normalizeCaseStudyPage(array $data): array
    {
        return $this->extractCaseStudyPageTranslation($data);
    }

    /** @return array<string, mixed> */
    private function saveServicePage(array $data): array
    {
        $page = $this->normalizeServicePage($data);
        $defaults = config('website.default_service_page', []);
        $existing = $this->decodeJson(Setting::get('website_service_page'), $defaults);
        $page['image'] = $this->saveSinglePageImage(
            $data,
            'service_page',
            (string) ($existing['image'] ?? $defaults['image'] ?? ''),
            (string) ($defaults['image'] ?? 'images/service/service-single-01.jpg'),
            'website/service'
        );

        return $page;
    }

    /** @return array<string, mixed> */
    private function saveBlogPage(array $data): array
    {
        $page = $this->normalizeBlogPage($data);
        $defaults = config('website.default_blog_page', []);
        $existing = $this->decodeJson(Setting::get('website_blog_page'), $defaults);
        $page['image'] = $this->saveSinglePageImage(
            $data,
            'blog_page',
            (string) ($existing['image'] ?? $defaults['image'] ?? ''),
            (string) ($defaults['image'] ?? 'images/blog/blog-img-01.jpg'),
            'website/blog'
        );

        return $page;
    }

    /** @return array<string, mixed> */
    private function saveCaseStudyPage(array $data): array
    {
        $page = $this->normalizeCaseStudyPage($data);
        $defaults = config('website.default_case_study_page', []);
        $existing = $this->decodeJson(Setting::get('website_case_study_page'), $defaults);

        foreach ([
            'before_image' => ['prefix' => 'case_study_page_before', 'default' => 'images/before-img-01.jpg', 'dir' => 'website/case-studies'],
            'after_image' => ['prefix' => 'case_study_page_after', 'default' => 'images/after-img-01.jpg', 'dir' => 'website/case-studies'],
            'detail_image1' => ['prefix' => 'case_study_page_detail1', 'default' => 'images/portfolio/portfolio-detail-01.jpg', 'dir' => 'website/case-studies'],
            'detail_image2' => ['prefix' => 'case_study_page_detail2', 'default' => 'images/portfolio/portfolio-detail-02.jpg', 'dir' => 'website/case-studies'],
        ] as $key => $meta) {
            $page[$key] = $this->saveSinglePageImage(
                $data,
                $meta['prefix'],
                (string) ($existing[$key] ?? $defaults[$key] ?? ''),
                (string) ($defaults[$key] ?? $meta['default']),
                $meta['dir']
            );
        }

        return $page;
    }

    private function saveSinglePageImage(array $data, string $prefix, string $current, string $defaultPath, string $storageDir): string
    {
        if (! empty($data[$prefix.'_remove_image'])) {
            if (str_starts_with($current, 'website/')) {
                $this->deleteStoredFile($current);
            }

            return $defaultPath;
        }

        if (($data[$prefix.'_image_file'] ?? null) instanceof UploadedFile) {
            if (str_starts_with($current, 'website/')) {
                $this->deleteStoredFile($current);
            }

            return $data[$prefix.'_image_file']->store($storageDir, 'public');
        }

        $hidden = trim((string) ($data[$prefix.'_image'] ?? ''));

        return $hidden !== '' ? $hidden : ($current !== '' ? $current : $defaultPath);
    }

    /** @param  array<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $stored
     * @return array<int, array<string, mixed>>
     */
    private function hydrateFeatureItems(array $items, array $stored): array
    {
        $legacyDetail = $this->legacyServicePageDetail($stored);
        $usedSlugs = [];
        $out = [];

        foreach ($items as $i => $item) {
            $title = trim($item['title'] ?? '');
            if ($title === '') {
                continue;
            }

            $slug = $this->resolveItemSlug($item, $item, $title, $usedSlugs);
            $usedSlugs[] = $slug;
            $detail = array_replace_recursive(
                config('website.default_service_page', []),
                is_array($item['detail'] ?? null) ? $item['detail'] : []
            );

            if ($i === 0 && $legacyDetail !== []) {
                $detail = array_replace_recursive($detail, $legacyDetail);
            }

            $out[] = array_merge($item, [
                'slug' => $slug,
                'detail' => $detail,
            ]);
        }

        return $out;
    }

    /** @param  array<int, array<string, mixed>>  $stored
     * @param  array<int, array<string, mixed>>  $defaults
     * @return array<int, array<string, mixed>>
     */
    private function mergeBlogItemsWithDefaults(array $stored, array $defaults): array
    {
        if ($defaults === []) {
            return $stored;
        }

        $knownTitles = collect($stored)
            ->map(fn (array $item) => Str::lower(trim($item['title'] ?? '')))
            ->filter()
            ->all();

        $knownSlugs = collect($stored)
            ->map(fn (array $item) => Str::slug(trim($item['slug'] ?? '')))
            ->filter()
            ->all();

        foreach ($defaults as $default) {
            $title = Str::lower(trim($default['title'] ?? ''));
            $slug = Str::slug(trim($default['slug'] ?? $default['title'] ?? ''));

            if ($title === '') {
                continue;
            }

            if (in_array($title, $knownTitles, true) || ($slug !== '' && in_array($slug, $knownSlugs, true))) {
                continue;
            }

            $stored[] = $default;
            $knownTitles[] = $title;

            if ($slug !== '') {
                $knownSlugs[] = $slug;
            }
        }

        return $stored;
    }

    /** @param  array<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $stored
     * @return array<int, array<string, mixed>>
     */
    private function hydrateBlogItems(array $items, array $stored): array
    {
        $legacyDetail = $this->legacyBlogPageDetail($stored);
        $usedSlugs = [];
        $out = [];

        foreach ($items as $i => $item) {
            $title = trim($item['title'] ?? '');
            if ($title === '') {
                continue;
            }

            $slug = $this->resolveItemSlug($item, $item, $title, $usedSlugs);
            $usedSlugs[] = $slug;
            $detail = array_replace_recursive(
                config('website.default_blog_page', []),
                is_array($item['detail'] ?? null) ? $item['detail'] : []
            );

            if ($i === 0 && $legacyDetail !== []) {
                $detail = array_replace_recursive($detail, $legacyDetail);
            }

            $out[] = array_merge($item, [
                'slug' => $slug,
                'detail' => $detail,
            ]);
        }

        return $out;
    }

    /** @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    private function legacyServicePageDetail(array $stored): array
    {
        $saved = $this->decodeJson($stored['website_service_page'] ?? null, []);

        return filled($saved['title'] ?? null) || filled($saved['intro'] ?? null) ? $saved : [];
    }

    /** @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    private function legacyBlogPageDetail(array $stored): array
    {
        $saved = $this->decodeJson($stored['website_blog_page'] ?? null, []);

        return filled($saved['title'] ?? null) || filled($saved['intro'] ?? null) ? $saved : [];
    }

    /** @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $existing
     * @param  array<int, string>  $usedSlugs
     */
    private function resolveItemSlug(array $row, array $existing, string $title, array $usedSlugs): string
    {
        $candidate = Str::slug(trim($row['slug'] ?? ''));

        if ($candidate === '') {
            $candidate = Str::slug(trim($existing['slug'] ?? ''));
        }

        if ($candidate === '') {
            $candidate = Str::slug($title);
        }

        return $this->ensureUniqueSlug($candidate !== '' ? $candidate : 'item', $usedSlugs);
    }

    /** @param  array<int, string>  $usedSlugs */
    private function ensureUniqueSlug(string $slug, array $usedSlugs): string
    {
        $base = Str::slug($slug) ?: 'item';
        $candidate = $base;
        $suffix = 2;

        while (in_array($candidate, $usedSlugs, true)) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    /** @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    private function normalizeServiceDetail(array $row, array $existing, string $cardTitle): array
    {
        $existingDetail = is_array($existing['detail'] ?? null) ? $existing['detail'] : [];
        $defaults = config('website.default_service_page', []);

        if (! array_key_exists('detail', $row)) {
            $detail = array_replace_recursive($defaults, $existingDetail);
            if (! filled($detail['title'] ?? null)) {
                $detail['title'] = $cardTitle;
            }

            return $detail;
        }

        $detailInput = is_array($row['detail'] ?? null) ? $row['detail'] : [];

        $detail = array_replace_recursive($defaults, $existingDetail, [
            'eyebrow' => trim($detailInput['eyebrow'] ?? ''),
            'title' => trim($detailInput['title'] ?? ''),
            'intro' => trim($detailInput['intro'] ?? ''),
            'body' => trim($detailInput['body'] ?? ''),
            'section2_title' => trim($detailInput['section2_title'] ?? ''),
            'section2_body' => trim($detailInput['section2_body'] ?? ''),
            'section3_title' => trim($detailInput['section3_title'] ?? ''),
            'section3_body' => trim($detailInput['section3_body'] ?? ''),
            'sidebar_heading' => trim($detailInput['sidebar_heading'] ?? ''),
            'sidebar_text' => trim($detailInput['sidebar_text'] ?? ''),
            'sidebar_services' => $this->normalizeMultilineList($detailInput['sidebar_services_text'] ?? ($detailInput['sidebar_services'] ?? ($existingDetail['sidebar_services'] ?? []))),
            'image' => trim($detailInput['image'] ?? ($existingDetail['image'] ?? '')),
        ]);

        if (! filled($detail['title'])) {
            $detail['title'] = $cardTitle;
        }

        return $detail;
    }

    /** @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    private function normalizeBlogDetail(array $row, array $existing, string $cardTitle): array
    {
        $detailInput = is_array($row['detail'] ?? null) ? $row['detail'] : [];
        $existingDetail = is_array($existing['detail'] ?? null) ? $existing['detail'] : [];
        $defaults = config('website.default_blog_page', []);

        $detail = array_replace_recursive($defaults, $existingDetail, [
            'title' => trim($detailInput['title'] ?? ''),
            'category' => trim($detailInput['category'] ?? ($row['category'] ?? '')),
            'date' => trim($detailInput['date'] ?? ($row['date'] ?? '')),
            'author' => trim($detailInput['author'] ?? ''),
            'author_bio' => trim($detailInput['author_bio'] ?? ''),
            'intro' => trim($detailInput['intro'] ?? ''),
            'section2_title' => trim($detailInput['section2_title'] ?? ''),
            'section2_body' => trim($detailInput['section2_body'] ?? ''),
            'section3_title' => trim($detailInput['section3_title'] ?? ''),
            'section3_body' => trim($detailInput['section3_body'] ?? ''),
            'quote' => trim($detailInput['quote'] ?? ''),
            'quote_author' => trim($detailInput['quote_author'] ?? ''),
            'tags' => $this->normalizeTagList($detailInput['tags'] ?? ''),
            'image' => trim($detailInput['image'] ?? ($existingDetail['image'] ?? '')),
        ]);

        if (! filled($detail['title'])) {
            $detail['title'] = $cardTitle;
        }

        return $detail;
    }

    /** @param  array<string, mixed>  $detailInput */
    private function saveNestedDetailImage(array $detailInput, string $current, string $storageDir): string
    {
        if (! empty($detailInput['remove_image'])) {
            if (str_starts_with($current, 'website/')) {
                $this->deleteStoredFile($current);
            }

            return '';
        }

        if (($detailInput['image_file'] ?? null) instanceof UploadedFile) {
            if (str_starts_with($current, 'website/')) {
                $this->deleteStoredFile($current);
            }

            return $detailInput['image_file']->store($storageDir, 'public');
        }

        $hidden = trim((string) ($detailInput['image'] ?? ''));

        return $hidden !== '' ? $hidden : $current;
    }

    /** @return array<string, mixed> */
    public function normalizeShowcaseDetailFromRequest(array $data, ?WebsiteShowcase $existing = null): array
    {
        $defaults = config('website.default_case_study_page', []);
        $existingDetail = is_array($existing?->detail) ? $existing->detail : [];

        $detail = array_replace_recursive($defaults, $existingDetail, [
            'title' => trim($data['detail_title'] ?? ''),
            'summary_title' => trim($data['detail_summary_title'] ?? ''),
            'sidebar_intro' => trim($data['detail_sidebar_intro'] ?? ''),
            'intro' => trim($data['detail_intro'] ?? ''),
            'body' => trim($data['detail_body'] ?? ''),
            'what_we_did_title' => trim($data['detail_what_we_did_title'] ?? ''),
            'what_we_did_body' => trim($data['detail_what_we_did_body'] ?? ''),
            'client' => trim($data['detail_client'] ?? ''),
            'category' => trim($data['detail_category'] ?? ''),
            'date' => trim($data['detail_date'] ?? ''),
            'location' => trim($data['detail_location'] ?? ''),
            'detail_image1' => trim($data['detail_image1'] ?? ($existingDetail['detail_image1'] ?? '')),
            'detail_image2' => trim($data['detail_image2'] ?? ($existingDetail['detail_image2'] ?? '')),
        ]);

        foreach ([
            'detail_image1' => ['file' => 'detail_image1_file', 'remove' => 'detail_image1_remove', 'dir' => 'website/case-studies'],
            'detail_image2' => ['file' => 'detail_image2_file', 'remove' => 'detail_image2_remove', 'dir' => 'website/case-studies'],
        ] as $key => $meta) {
            if (! empty($data[$meta['remove']])) {
                if (str_starts_with($detail[$key], 'website/')) {
                    $this->deleteStoredFile($detail[$key]);
                }
                $detail[$key] = $defaults[$key] ?? '';
                continue;
            }

            if (($data[$meta['file']] ?? null) instanceof UploadedFile) {
                if (str_starts_with($detail[$key], 'website/')) {
                    $this->deleteStoredFile($detail[$key]);
                }
                $detail[$key] = $data[$meta['file']]->store($meta['dir'], 'public');
            }
        }

        return $detail;
    }

    /** @param  array<int, string>  $usedSlugs */
    public function resolveShowcaseSlug(array $data, ?WebsiteShowcase $existing = null, array $usedSlugs = []): string
    {
        $title = trim($data['title'] ?? ($existing?->title ?? ''));

        return $this->resolveItemSlug(
            $data,
            ['slug' => $existing?->slug ?? ''],
            $title,
            $usedSlugs
        );
    }

    public function pageImageUrl(?string $path, string $defaultPath): string
    {
        $path = trim((string) ($path ?? ''));

        if ($path !== '' && str_starts_with($path, 'website/')) {
            $url = PublicStorageUrl::url($path);

            if ($url !== null) {
                return $url;
            }
        }

        if ($path !== '' && ! str_starts_with($path, 'website/')) {
            return $this->smilizAsset($path);
        }

        return $this->smilizAsset($defaultPath);
    }

    public function titleBarImageUrl(): string
    {
        return $this->pageImageUrl(
            Setting::get('website_titlebar_image'),
            config('website.default_titlebar_image', 'images/bg/titlebar-bg.jpg')
        );
    }
}
