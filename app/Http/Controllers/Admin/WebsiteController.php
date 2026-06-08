<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteShowcase;
use App\Services\SmilizPageRegistry;
use App\Services\WebsiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function index(Request $request, WebsiteContent $website, SmilizPageRegistry $pages): View
    {
        $resolvedPages = $pages->resolvedSettings();
        $editLocale = strtolower($request->get('locale', 'en'));
        $locales = config('website-locales.locales', []);

        if (! array_key_exists($editLocale, $locales)) {
            $editLocale = 'en';
        }

        $pageCollection = collect($resolvedPages);

        return view('admin.website.index', [
            'content' => $website->allForAdmin($editLocale),
            'editLocale' => $editLocale,
            'locales' => $locales,
            'enabledLocales' => array_keys($website->enabledLocales()),
            'readiness' => $website->readiness(),
            'contentInventory' => $website->contentInventory(),
            'heroImageUrl' => $website->heroImageUrl(),
            'heroVideoUrl' => $website->heroVideoUrl(),
            'logoUrl' => \App\Models\Setting::logoUrl(),
            'projectName' => \App\Models\Setting::projectName(),
            'showcases' => WebsiteShowcase::query()->orderBy('sort_order')->orderBy('id')->get(),
            'caseTypes' => config('website.case_types', []),
            'defaultFeatures' => config('website.default_features', []),
            'defaultSlides' => config('website.default_hero_slides', []),
            'defaultProcess' => config('website.default_process_steps', []),
            'defaultSections' => config('website.default_sections', []),
            'templates' => config('website.templates', []),
            'loginPath' => config('website.login_path', '/login'),
            'iconOptions' => $this->iconOptions(),
            'smilizIconOptions' => $this->smilizIconOptions(),
            'websiteContent' => $website,
            'portfolioUsesDemo' => $website->portfolioUsesDemoImages(),
            'portfolioDemoCount' => count(config('website.default_showcases', [])),
            'portfolioPreviewItems' => $website->portfolioItems(),
            'smilizPages' => $resolvedPages,
            'pageGroups' => config('smiliz-pages.groups', []),
            'pageGroupHints' => config('smiliz-pages.group_hints', []),
            'blogKeyPattern' => config('smiliz-pages.blog_key_pattern', '/^blog-/'),
            'caseStudyKeyPattern' => config('smiliz-pages.case_study_key_pattern', '/^(portfolio-|case-study-)/'),
            'enabledPageCount' => $pageCollection->where('enabled', true)->count(),
            'navInMenuCount' => $pageCollection->where('enabled', true)->where('in_nav', true)->count(),
            'navHiddenCount' => $pageCollection->where('enabled', false)->count(),
            'totalPageCount' => count($resolvedPages),
            'menuPreview' => $pages->navigation($editLocale),
            'mainMenuEntries' => $pages->mainMenuAdminEntries(),
            'arabicNavCoverage' => $pages->arabicNavCoverage(),
            'pageLinkOptions' => $pageCollection->mapWithKeys(fn (array $page) => [
                $page['key'] => $page['nav_label'].' (/'.$page['path'].')',
            ])->all(),
        ]);
    }

    public function updateMainMenu(Request $request, SmilizPageRegistry $pages): RedirectResponse
    {
        $request->validate([
            'main_menu' => ['nullable', 'array'],
            'main_menu.order' => ['nullable', 'array'],
            'main_menu.order.*' => ['nullable', 'string', 'max:64'],
            'main_menu.children' => ['nullable', 'array'],
            'main_menu.children.*' => ['nullable', 'array'],
            'main_menu.children.*.*' => ['nullable', 'string', 'max:120'],
            'main_menu.labels' => ['nullable', 'array'],
            'main_menu.labels.*' => ['nullable', 'string', 'max:120'],
            'main_menu.labels_ar' => ['nullable', 'array'],
            'main_menu.labels_ar.*' => ['nullable', 'string', 'max:120'],
            'main_menu.pages' => ['nullable', 'array'],
            'main_menu.pages.*.enabled' => ['sometimes', 'boolean'],
            'main_menu.pages.*.in_nav' => ['sometimes', 'boolean'],
            'main_menu.pages.*.nav_label' => ['nullable', 'string', 'max:120'],
            'main_menu.pages.*.nav_label_ar' => ['nullable', 'string', 'max:120'],
        ]);

        $pages->saveMainMenu($request->input('main_menu', []));

        return redirect()
            ->route('admin.website.index', ['section' => 'main-menu'])
            ->with('success', 'Main menu updated.');
    }

    public function updatePages(Request $request, SmilizPageRegistry $pages): RedirectResponse
    {
        $request->validate([
            'pages' => ['nullable', 'array'],
            'pages.*.enabled' => ['sometimes', 'boolean'],
            'pages.*.in_nav' => ['sometimes', 'boolean'],
            'pages.*.nav_label' => ['nullable', 'string', 'max:120'],
            'pages.*.nav_label_ar' => ['nullable', 'string', 'max:120'],
        ]);

        $pages->saveSettings($request->input('pages', []));

        return redirect()
            ->route('admin.website.index', ['section' => 'main-menu'])
            ->with('success', 'Website pages updated.');
    }

    public function updateContent(Request $request, WebsiteContent $website): RedirectResponse
    {
        $availableTemplates = collect(config('website.templates', []))
            ->filter(fn (array $meta) => ! empty($meta['available']))
            ->keys()
            ->all();

        $localeKeys = array_keys(config('website-locales.locales', []));
        $editLocale = strtolower((string) $request->input('edit_locale', 'en'));

        if (! in_array($editLocale, $localeKeys, true)) {
            $editLocale = 'en';
        }

        $data = $request->validate($this->contentValidationRules($editLocale, $availableTemplates, $localeKeys));

        $data['published'] = $request->boolean('published');
        $data['edit_locale'] = $editLocale;
        $data['hero_image'] = $request->file('hero_image');
        $data['remove_hero_image'] = $request->boolean('remove_hero_image');
        $data['about_image'] = $request->file('about_image');
        $data['remove_about_image'] = $request->boolean('remove_about_image');
        $data['titlebar_image'] = $request->file('titlebar_image');
        $data['remove_titlebar_image'] = $request->boolean('remove_titlebar_image');
        $data['footer_image'] = $request->file('footer_image');
        $data['remove_footer_image'] = $request->boolean('remove_footer_image');
        $data['hero_video'] = $request->file('hero_video');
        $data['remove_hero_video'] = $request->boolean('remove_hero_video');
        $data['slides'] = collect($request->input('slides', []))
            ->map(function (array $slide, int $index) use ($request) {
                $slide['image_file'] = $request->file("slides.$index.image_file");
                $slide['remove_image'] = $request->boolean("slides.$index.remove_image");

                return $slide;
            })
            ->all();
        $data['treatable_items'] = collect($request->input('treatable_items', []))
            ->map(function (array $item, int $index) use ($request) {
                $item['image_file'] = $request->file("treatable_items.$index.image_file");
                $item['remove_image'] = $request->boolean("treatable_items.$index.remove_image");

                return $item;
            })
            ->all();
        $data['blog_posts'] = collect($request->input('blog_posts', []))
            ->map(function (array $item, int $index) use ($request) {
                $item['image_file'] = $request->file("blog_posts.$index.image_file");
                $item['remove_image'] = $request->boolean("blog_posts.$index.remove_image");

                return $item;
            })
            ->all();
        $data['process_steps'] = collect($request->input('process_steps', []))
            ->map(function (array $item, int $index) use ($request) {
                $item['image_file'] = $request->file("process_steps.$index.image_file");
                $item['remove_image'] = $request->boolean("process_steps.$index.remove_image");

                return $item;
            })
            ->all();
        $data['features'] = collect($request->input('features', []))
            ->map(function (array $feature, int $index) use ($request) {
                $feature['image_file'] = $request->file("features.$index.image_file");
                $feature['remove_image'] = $request->boolean("features.$index.remove_image");

                return $feature;
            })
            ->all();
        $data['sections'] = collect(config('website.default_sections', []))
            ->mapWithKeys(function ($default, $key) use ($request, $website) {
                if ($request->has("sections.$key")) {
                    return [$key => $request->boolean("sections.$key")];
                }

                return [$key => $website->all()['sections'][$key] ?? $default];
            })
            ->all();
        $data['navigation'] = $request->input('navigation', []);
        if ($request->has('navigation.services_column')) {
            $data['navigation']['services_column']['enabled'] = $request->boolean('navigation.services_column.enabled');
            $data['navigation']['services_column']['use_features'] = $request->boolean('navigation.services_column.use_features');
        }
        if ($request->has('navigation.newsletter')) {
            $data['navigation']['newsletter']['enabled'] = $request->boolean('navigation.newsletter.enabled');
        }

        $website->saveContent($data, $editLocale);

        if ($request->has('enabled_locales')) {
            $website->saveEnabledLocales($request->input('enabled_locales', []));
        }

        $localeLabel = config('website-locales.locales.'.$editLocale.'.native', $editLocale);
        $message = $editLocale === 'en'
            ? 'Website content saved.'
            : $localeLabel.' translation saved.';

        return redirect()
            ->route('admin.website.index', [
                'section' => $request->input('return_tab', 'general'),
                'locale' => $editLocale,
            ])
            ->with('success', $message);
    }

    public function storeShowcase(Request $request, WebsiteContent $website): RedirectResponse
    {
        $data = $this->validateShowcase($request);
        $section = $this->showcaseReturnSection($request);
        $data['sort_order'] = WebsiteShowcase::nextSortOrder();
        $data = array_merge($data, $this->storeShowcaseImages($request));
        $data['slug'] = $website->resolveShowcaseSlug($request->all());
        $data['detail'] = $website->normalizeShowcaseDetailFromRequest($request->all());

        WebsiteShowcase::create($data);

        return redirect()
            ->route('admin.website.index', ['section' => $section])
            ->with('success', $this->showcaseSuccessMessage('added', $section));
    }

    public function updateShowcase(Request $request, WebsiteShowcase $showcase, WebsiteContent $website): RedirectResponse
    {
        $data = $this->validateShowcase($request);
        $section = $this->showcaseReturnSection($request);
        $data = array_merge($data, $this->storeShowcaseImages($request, $showcase));
        $usedSlugs = WebsiteShowcase::query()
            ->where('id', '!=', $showcase->id)
            ->pluck('slug')
            ->filter()
            ->all();
        $data['slug'] = $website->resolveShowcaseSlug($request->all(), $showcase, $usedSlugs);
        $data['detail'] = $website->normalizeShowcaseDetailFromRequest($request->all(), $showcase);

        $showcase->update($data);

        return redirect()
            ->route('admin.website.index', ['section' => $section])
            ->with('success', $this->showcaseSuccessMessage('updated', $section));
    }

    public function destroyShowcase(Request $request, WebsiteShowcase $showcase): RedirectResponse
    {
        $section = $this->showcaseReturnSection($request);
        $this->deleteShowcaseImage($showcase->before_image);
        $this->deleteShowcaseImage($showcase->after_image);
        $showcase->delete();

        return redirect()
            ->route('admin.website.index', ['section' => $section])
            ->with('success', $this->showcaseSuccessMessage('removed', $section));
    }

    private function showcaseReturnSection(Request $request): string
    {
        $section = $request->input('return_section');

        return in_array($section, ['portfolio', 'case-studies'], true) ? $section : 'case-studies';
    }

    private function showcaseSuccessMessage(string $action, string $section): string
    {
        $label = $section === 'portfolio' ? 'Case result' : 'Case study';

        return match ($action) {
            'added' => $label.' added.',
            'updated' => $label.' updated.',
            'removed' => $label.' removed.',
            default => 'Saved.',
        };
    }

    private function validateShowcase(Request $request): array
    {
        $caseTypes = array_keys(config('website.case_types', []));

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:120'],
            'patient_label' => ['nullable', 'string', 'max:120'],
            'case_type' => ['required', 'in:'.implode(',', $caseTypes)],
            'treatment_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'outcome' => ['nullable', 'string', 'max:2000'],
            'is_published' => ['sometimes', 'boolean'],
            'before_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'after_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'remove_before_image' => ['sometimes', 'boolean'],
            'remove_after_image' => ['sometimes', 'boolean'],
            'detail_title' => ['nullable', 'string', 'max:255'],
            'detail_summary_title' => ['nullable', 'string', 'max:255'],
            'detail_sidebar_intro' => ['nullable', 'string', 'max:2000'],
            'detail_intro' => ['nullable', 'string', 'max:5000'],
            'detail_body' => ['nullable', 'string', 'max:5000'],
            'detail_what_we_did_title' => ['nullable', 'string', 'max:255'],
            'detail_what_we_did_body' => ['nullable', 'string', 'max:5000'],
            'detail_client' => ['nullable', 'string', 'max:120'],
            'detail_category' => ['nullable', 'string', 'max:120'],
            'detail_date' => ['nullable', 'string', 'max:80'],
            'detail_location' => ['nullable', 'string', 'max:255'],
            'detail_image1' => ['nullable', 'string', 'max:500'],
            'detail_image1_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'detail_image1_remove' => ['sometimes', 'boolean'],
            'detail_image2' => ['nullable', 'string', 'max:500'],
            'detail_image2_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'detail_image2_remove' => ['sometimes', 'boolean'],
        ]) + [
            'is_published' => $request->boolean('is_published', true),
            'treatment_months' => $request->input('treatment_months') ?: null,
        ];
    }

    private function storeShowcaseImages(Request $request, ?WebsiteShowcase $existing = null): array
    {
        $paths = [];

        foreach (['before_image', 'after_image'] as $field) {
            $removeKey = 'remove_'.$field;

            if ($request->boolean($removeKey)) {
                if ($existing) {
                    $this->deleteShowcaseImage($existing->{$field});
                }
                $paths[$field] = null;
                continue;
            }

            if ($request->hasFile($field)) {
                if ($existing) {
                    $this->deleteShowcaseImage($existing->{$field});
                }
                $paths[$field] = $request->file($field)->store('website/showcases', 'public');
            }
        }

        return $paths;
    }

    private function deleteShowcaseImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /** @return array<string, mixed> */
    private function contentValidationRules(string $editLocale, array $availableTemplates, array $localeKeys): array
    {
        $heroRequired = $editLocale === 'en' ? 'required' : 'nullable';

        return [
            'edit_locale' => ['nullable', 'string', 'in:'.implode(',', $localeKeys)],
            'enabled_locales' => ['nullable', 'array'],
            'enabled_locales.*' => ['string', 'in:'.implode(',', $localeKeys)],
            'published' => ['sometimes', 'boolean'],
            'website_template' => ['nullable', 'string', 'in:'.implode(',', $availableTemplates)],
            'slides' => ['nullable', 'array'],
            'slides.*.eyebrow' => ['nullable', 'string', 'max:120'],
            'slides.*.title' => ['nullable', 'string', 'max:255'],
            'slides.*.cta_label' => ['nullable', 'string', 'max:80'],
            'slides.*.image' => ['nullable', 'string', 'max:500'],
            'slides.*.image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'slides.*.remove_image' => ['sometimes', 'boolean'],
            'hero_eyebrow' => ['nullable', 'string', 'max:120'],
            'hero_title' => [$heroRequired, 'string', 'max:255'],
            'hero_subtitle' => [$heroRequired, 'string', 'max:2000'],
            'hero_cta_label' => ['nullable', 'string', 'max:80'],
            'hero_cta_url' => ['nullable', 'string', 'max:500'],
            'hero_type' => ['nullable', 'string', 'in:video,slider'],
            'hero_video' => ['nullable', 'file', 'mimes:mp4,webm', 'max:51200'],
            'remove_hero_video' => ['sometimes', 'boolean'],
            'hero_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'remove_hero_image' => ['sometimes', 'boolean'],
            'about_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'remove_about_image' => ['sometimes', 'boolean'],
            'titlebar_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'remove_titlebar_image' => ['sometimes', 'boolean'],
            'footer_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'remove_footer_image' => ['sometimes', 'boolean'],
            'about_subtitle' => ['nullable', 'string', 'max:120'],
            'about_title' => ['nullable', 'string', 'max:255'],
            'about_body' => ['nullable', 'string', 'max:5000'],
            'about_years' => ['nullable', 'integer', 'min:1', 'max:100'],
            'about_years_label' => ['nullable', 'string', 'max:120'],
            'about_highlights' => ['nullable', 'array'],
            'about_highlights.*.title' => ['nullable', 'string', 'max:120'],
            'about_highlights.*.description' => ['nullable', 'string', 'max:500'],
            'about_page_title' => ['nullable', 'string', 'max:120'],
            'about_page_partner_title' => ['nullable', 'string', 'max:500'],
            'about_page_team_subtitle' => ['nullable', 'string', 'max:120'],
            'about_page_team_title' => ['nullable', 'string', 'max:255'],
            'about_page_process_divider' => ['nullable', 'string', 'max:120'],
            'about_page_testimonial_subtitle' => ['nullable', 'string', 'max:120'],
            'about_page_discover_label' => ['nullable', 'string', 'max:80'],
            'about_page_show_team' => ['sometimes', 'boolean'],
            'about_page_show_testimonials' => ['sometimes', 'boolean'],
            'platform_subtitle' => ['nullable', 'string', 'max:120'],
            'platform_title' => ['nullable', 'string', 'max:255'],
            'platform_intro' => ['nullable', 'string', 'max:2000'],
            'platform_cta_label' => ['nullable', 'string', 'max:80'],
            'platform_cta_url' => ['nullable', 'string', 'max:500'],
            'features' => ['nullable', 'array'],
            'features.*.icon' => ['nullable', 'string', 'max:64'],
            'features.*.title' => ['nullable', 'string', 'max:120'],
            'features.*.description' => ['nullable', 'string', 'max:500'],
            'features.*.button_label' => ['nullable', 'string', 'max:80'],
            'features.*.link_url' => ['nullable', 'string', 'max:500'],
            'features.*.slug' => ['nullable', 'string', 'max:120'],
            'features.*.detail' => ['nullable', 'array'],
            'features.*.detail.eyebrow' => ['nullable', 'string', 'max:120'],
            'features.*.detail.title' => ['nullable', 'string', 'max:255'],
            'features.*.detail.intro' => ['nullable', 'string', 'max:5000'],
            'features.*.detail.body' => ['nullable', 'string', 'max:5000'],
            'features.*.detail.section2_title' => ['nullable', 'string', 'max:255'],
            'features.*.detail.section2_body' => ['nullable', 'string', 'max:5000'],
            'features.*.detail.section3_title' => ['nullable', 'string', 'max:255'],
            'features.*.detail.section3_body' => ['nullable', 'string', 'max:5000'],
            'features.*.detail.sidebar_heading' => ['nullable', 'string', 'max:255'],
            'features.*.detail.sidebar_text' => ['nullable', 'string', 'max:1000'],
            'features.*.detail.sidebar_services_text' => ['nullable', 'string', 'max:2000'],
            'features.*.detail.image' => ['nullable', 'string', 'max:500'],
            'features.*.detail.image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'features.*.detail.remove_image' => ['sometimes', 'boolean'],
            'features.*.image' => ['nullable', 'string', 'max:500'],
            'features.*.image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'features.*.remove_image' => ['sometimes', 'boolean'],
            'stats' => ['nullable', 'array'],
            'stats.*.value' => ['nullable', 'string', 'max:32'],
            'stats.*.label' => ['nullable', 'string', 'max:80'],
            'stats_subtitle' => ['nullable', 'string', 'max:120'],
            'stats_title' => ['nullable', 'string', 'max:255'],
            'stats_cta_label' => ['nullable', 'string', 'max:80'],
            'stats_cta_title' => ['nullable', 'string', 'max:255'],
            'process_subtitle' => ['nullable', 'string', 'max:120'],
            'process_title' => ['nullable', 'string', 'max:255'],
            'process_steps' => ['nullable', 'array'],
            'process_steps.*.title' => ['nullable', 'string', 'max:120'],
            'process_steps.*.description' => ['nullable', 'string', 'max:500'],
            'process_steps.*.image' => ['nullable', 'string', 'max:500'],
            'process_steps.*.image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'process_steps.*.remove_image' => ['sometimes', 'boolean'],
            'faq_subtitle' => ['nullable', 'string', 'max:120'],
            'faq_title' => ['nullable', 'string', 'max:255'],
            'faq_items' => ['nullable', 'array'],
            'faq_items.*.question' => ['nullable', 'string', 'max:255'],
            'faq_items.*.answer' => ['nullable', 'string', 'max:2000'],
            'partner_quote' => ['nullable', 'string', 'max:500'],
            'partner_title' => ['nullable', 'string', 'max:255'],
            'partner_body' => ['nullable', 'string', 'max:1000'],
            'partner_cta_label' => ['nullable', 'string', 'max:80'],
            'footer_tagline' => ['nullable', 'string', 'max:500'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_hours' => ['nullable', 'string', 'max:120'],
            'contact_address' => ['nullable', 'string', 'max:255'],
            'contact_page_subtitle' => ['nullable', 'string', 'max:120'],
            'contact_page_title' => ['nullable', 'string', 'max:255'],
            'contact_page_intro' => ['nullable', 'string', 'max:2000'],
            'contact_page_email_title' => ['nullable', 'string', 'max:120'],
            'contact_page_phone_title' => ['nullable', 'string', 'max:120'],
            'contact_page_location_title' => ['nullable', 'string', 'max:120'],
            'contact_page_form_title' => ['nullable', 'string', 'max:255'],
            'contact_page_form_intro' => ['nullable', 'string', 'max:500'],
            'service_page_eyebrow' => ['nullable', 'string', 'max:120'],
            'service_page_title' => ['nullable', 'string', 'max:255'],
            'service_page_intro' => ['nullable', 'string', 'max:5000'],
            'service_page_body' => ['nullable', 'string', 'max:5000'],
            'service_page_section2_title' => ['nullable', 'string', 'max:255'],
            'service_page_section2_body' => ['nullable', 'string', 'max:5000'],
            'service_page_section3_title' => ['nullable', 'string', 'max:255'],
            'service_page_section3_body' => ['nullable', 'string', 'max:5000'],
            'service_page_sidebar_heading' => ['nullable', 'string', 'max:255'],
            'service_page_sidebar_text' => ['nullable', 'string', 'max:1000'],
            'service_page_sidebar_services_text' => ['nullable', 'string', 'max:2000'],
            'service_page_image' => ['nullable', 'string', 'max:500'],
            'service_page_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'service_page_remove_image' => ['sometimes', 'boolean'],
            'blog_page_title' => ['nullable', 'string', 'max:255'],
            'blog_page_category' => ['nullable', 'string', 'max:80'],
            'blog_page_date' => ['nullable', 'string', 'max:80'],
            'blog_page_author' => ['nullable', 'string', 'max:120'],
            'blog_page_author_bio' => ['nullable', 'string', 'max:2000'],
            'blog_page_intro' => ['nullable', 'string', 'max:5000'],
            'blog_page_section2_title' => ['nullable', 'string', 'max:255'],
            'blog_page_section2_body' => ['nullable', 'string', 'max:5000'],
            'blog_page_section3_title' => ['nullable', 'string', 'max:255'],
            'blog_page_section3_body' => ['nullable', 'string', 'max:5000'],
            'blog_page_quote' => ['nullable', 'string', 'max:1000'],
            'blog_page_quote_author' => ['nullable', 'string', 'max:120'],
            'blog_page_tags' => ['nullable', 'string', 'max:500'],
            'blog_page_image' => ['nullable', 'string', 'max:500'],
            'blog_page_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'blog_page_remove_image' => ['sometimes', 'boolean'],
            'case_study_page_title' => ['nullable', 'string', 'max:255'],
            'case_study_page_summary_title' => ['nullable', 'string', 'max:255'],
            'case_study_page_sidebar_intro' => ['nullable', 'string', 'max:2000'],
            'case_study_page_intro' => ['nullable', 'string', 'max:5000'],
            'case_study_page_body' => ['nullable', 'string', 'max:5000'],
            'case_study_page_what_we_did_title' => ['nullable', 'string', 'max:255'],
            'case_study_page_what_we_did_body' => ['nullable', 'string', 'max:5000'],
            'case_study_page_client' => ['nullable', 'string', 'max:120'],
            'case_study_page_category' => ['nullable', 'string', 'max:120'],
            'case_study_page_date' => ['nullable', 'string', 'max:80'],
            'case_study_page_location' => ['nullable', 'string', 'max:255'],
            'case_study_page_before_image' => ['nullable', 'string', 'max:500'],
            'case_study_page_before_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'case_study_page_before_remove_image' => ['sometimes', 'boolean'],
            'case_study_page_after_image' => ['nullable', 'string', 'max:500'],
            'case_study_page_after_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'case_study_page_after_remove_image' => ['sometimes', 'boolean'],
            'case_study_page_detail1_image' => ['nullable', 'string', 'max:500'],
            'case_study_page_detail1_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'case_study_page_detail1_remove_image' => ['sometimes', 'boolean'],
            'case_study_page_detail2_image' => ['nullable', 'string', 'max:500'],
            'case_study_page_detail2_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'case_study_page_detail2_remove_image' => ['sometimes', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:120'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'cta_rating' => ['nullable', 'string', 'max:32'],
            'cta_rating_label' => ['nullable', 'string', 'max:255'],
            'cta_subtitle' => ['nullable', 'string', 'max:255'],
            'cta_title' => ['nullable', 'string', 'max:255'],
            'cta_banner_label' => ['nullable', 'string', 'max:80'],
            'sections' => ['nullable', 'array'],
            'sections.*' => ['sometimes', 'boolean'],
            'treatments_subtitle' => ['nullable', 'string', 'max:120'],
            'treatments_title' => ['nullable', 'string', 'max:255'],
            'treatments_intro' => ['nullable', 'string', 'max:2000'],
            'case_studies_subtitle' => ['nullable', 'string', 'max:120'],
            'case_studies_title' => ['nullable', 'string', 'max:255'],
            'treatable_subtitle' => ['nullable', 'string', 'max:120'],
            'treatable_title' => ['nullable', 'string', 'max:255'],
            'treatable_intro' => ['nullable', 'string', 'max:2000'],
            'treatable_items' => ['nullable', 'array'],
            'treatable_items.*.title' => ['nullable', 'string', 'max:120'],
            'treatable_items.*.description' => ['nullable', 'string', 'max:500'],
            'treatable_items.*.image' => ['nullable', 'string', 'max:500'],
            'treatable_items.*.image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'treatable_items.*.remove_image' => ['sometimes', 'boolean'],
            'blog_subtitle' => ['nullable', 'string', 'max:120'],
            'blog_title' => ['nullable', 'string', 'max:255'],
            'blog_posts' => ['nullable', 'array'],
            'blog_posts.*.title' => ['nullable', 'string', 'max:255'],
            'blog_posts.*.excerpt' => ['nullable', 'string', 'max:500'],
            'blog_posts.*.category' => ['nullable', 'string', 'max:80'],
            'blog_posts.*.date' => ['nullable', 'string', 'max:80'],
            'blog_posts.*.url' => ['nullable', 'string', 'max:500'],
            'blog_posts.*.image' => ['nullable', 'string', 'max:500'],
            'blog_posts.*.image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'blog_posts.*.remove_image' => ['sometimes', 'boolean'],
            'blog_posts.*.slug' => ['nullable', 'string', 'max:120'],
            'blog_posts.*.detail' => ['nullable', 'array'],
            'blog_posts.*.detail.title' => ['nullable', 'string', 'max:255'],
            'blog_posts.*.detail.category' => ['nullable', 'string', 'max:80'],
            'blog_posts.*.detail.date' => ['nullable', 'string', 'max:80'],
            'blog_posts.*.detail.author' => ['nullable', 'string', 'max:120'],
            'blog_posts.*.detail.author_bio' => ['nullable', 'string', 'max:2000'],
            'blog_posts.*.detail.intro' => ['nullable', 'string', 'max:5000'],
            'blog_posts.*.detail.section2_title' => ['nullable', 'string', 'max:255'],
            'blog_posts.*.detail.section2_body' => ['nullable', 'string', 'max:5000'],
            'blog_posts.*.detail.section3_title' => ['nullable', 'string', 'max:255'],
            'blog_posts.*.detail.section3_body' => ['nullable', 'string', 'max:5000'],
            'blog_posts.*.detail.quote' => ['nullable', 'string', 'max:1000'],
            'blog_posts.*.detail.quote_author' => ['nullable', 'string', 'max:120'],
            'blog_posts.*.detail.tags' => ['nullable', 'string', 'max:500'],
            'blog_posts.*.detail.image' => ['nullable', 'string', 'max:500'],
            'blog_posts.*.detail.image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'blog_posts.*.detail.remove_image' => ['sometimes', 'boolean'],
            'practice_care_subtitle' => ['nullable', 'string', 'max:120'],
            'practice_care_title' => ['nullable', 'string', 'max:255'],
            'practice_care_items' => ['nullable', 'array'],
            'practice_care_items.*.smiliz_icon' => ['nullable', 'string', 'max:120'],
            'practice_care_items.*.title' => ['nullable', 'string', 'max:120'],
            'practice_care_items.*.description' => ['nullable', 'string', 'max:500'],
            'practice_care_items.*.button_label' => ['nullable', 'string', 'max:80'],
            'practice_care_items.*.link_url' => ['nullable', 'string', 'max:500'],
            'practice_care_cta_icon' => ['nullable', 'string', 'max:120'],
            'practice_care_cta_title' => ['nullable', 'string', 'max:255'],
            'practice_care_cta_label' => ['nullable', 'string', 'max:80'],
            'practice_care_cta_url' => ['nullable', 'string', 'max:500'],
            'navigation' => ['nullable', 'array'],
            'navigation.social_links' => ['nullable', 'array'],
            'navigation.social_links.*.network' => ['nullable', 'string', 'max:32'],
            'navigation.social_links.*.title' => ['nullable', 'string', 'max:80'],
            'navigation.social_links.*.url' => ['nullable', 'string', 'max:500'],
            'navigation.footer_columns' => ['nullable', 'array'],
            'navigation.footer_columns.*.title' => ['nullable', 'string', 'max:120'],
            'navigation.footer_columns.*.links' => ['nullable', 'array'],
            'navigation.footer_columns.*.links.*.label' => ['nullable', 'string', 'max:120'],
            'navigation.footer_columns.*.links.*.type' => ['nullable', 'string', 'in:page,anchor,home,url'],
            'navigation.footer_columns.*.links.*.page_key' => ['nullable', 'string', 'max:120'],
            'navigation.footer_columns.*.links.*.url' => ['nullable', 'string', 'max:500'],
            'navigation.services_column.enabled' => ['sometimes', 'boolean'],
            'navigation.services_column.title' => ['nullable', 'string', 'max:120'],
            'navigation.services_column.use_features' => ['sometimes', 'boolean'],
            'navigation.services_column.feature_limit' => ['nullable', 'integer', 'min:1', 'max:10'],
            'navigation.services_column.feature_link' => ['nullable', 'string', 'max:120'],
            'navigation.newsletter.enabled' => ['sometimes', 'boolean'],
            'navigation.newsletter.title' => ['nullable', 'string', 'max:120'],
            'navigation.newsletter.blurb' => ['nullable', 'string', 'max:500'],
            'navigation.footer_utility' => ['nullable', 'array'],
            'navigation.footer_utility.*.label' => ['nullable', 'string', 'max:120'],
            'navigation.footer_utility.*.source' => ['nullable', 'string', 'in:phone,email,address,chat'],
            'navigation.footer_utility.*.chat_label' => ['nullable', 'string', 'max:80'],
            'navigation.bottom_links' => ['nullable', 'array'],
            'navigation.bottom_links.*.label' => ['nullable', 'string', 'max:120'],
            'navigation.bottom_links.*.type' => ['nullable', 'string', 'in:page,anchor,home,url'],
            'navigation.bottom_links.*.page_key' => ['nullable', 'string', 'max:120'],
            'navigation.bottom_links.*.url' => ['nullable', 'string', 'max:500'],
        ];
    }

    private function iconOptions(): array
    {
        return [
            'zmdi-folder',
            'zmdi-rotate-3d',
            'zmdi-assignment-check',
            'zmdi-comment-text',
            'zmdi-refresh-sync',
            'zmdi-notifications',
            'zmdi-hospital',
            'zmdi-account',
            'zmdi-chart',
            'zmdi-shield-check',
            'zmdi-time',
            'zmdi-star',
        ];
    }

    /** @return array<string, string> */
    private function smilizIconOptions(): array
    {
        return [
            'pbmit-smiliz-icon-dental-crown' => 'Dental crown',
            'pbmit-smiliz-icon-inject' => 'Injection / general care',
            'pbmit-smiliz-icon-braces' => 'Braces / restorative',
            'pbmit-smiliz-icon-toothbrush' => 'Toothbrush / prevention',
            'pbmit-smiliz-icon-dental-care' => 'Dental care',
            'pbmit-smiliz-icon-dental-checkup' => 'Dental checkup',
            'pbmit-smiliz-icon-dental-chair' => 'Dental chair',
            'pbmit-smiliz-icon-dental-tools' => 'Dental tools',
            'pbmit-smiliz-icon-dental-bag' => 'Dental bag',
            'pbmit-smiliz-icon-teeth-brushing' => 'Teeth brushing',
            'pbmit-smiliz-icon-dental-safety' => 'Dental safety',
            'pbmit-smiliz-icon-star' => 'Star',
            'pbmit-smiliz-icon-contrast' => 'Contrast / clarity',
        ];
    }
}
