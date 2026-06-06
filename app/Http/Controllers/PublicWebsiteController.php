<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\SmilizHtmlRenderer;
use App\Services\SmilizPageRegistry;
use App\Services\WebsiteContent;
use App\Services\WebsiteLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicWebsiteController extends Controller
{
    public function show(Request $request, WebsiteContent $website): View
    {
        if (! $this->canViewSite($request, $website)) {
            return view('website.unpublished', [
                'projectName' => Setting::projectName(),
                'logoUrl' => Setting::logoUrl(),
            ]);
        }

        $view = $this->resolveTemplateView($website->all()['template'] ?? config('website.default_template'));

        return view($view, array_merge($this->sharedViewData($request, $website), [
            'canonicalUrl' => app(WebsiteLocale::class)->homeUrl(),
        ]));
    }

    public function page(Request $request, string $pageKey, WebsiteContent $website, SmilizHtmlRenderer $renderer, SmilizPageRegistry $registry): View|RedirectResponse
    {
        if (! $this->canViewSite($request, $website)) {
            return view('website.unpublished', [
                'projectName' => Setting::projectName(),
                'logoUrl' => Setting::logoUrl(),
            ]);
        }

        $locale = app(WebsiteLocale::class)->current();

        if ($redirect = $this->legacyDetailRedirect($pageKey, $website, $registry, $locale)) {
            return $redirect;
        }

        return $this->renderStaticPage($request, $pageKey, $website, $renderer, $registry);
    }

    public function serviceDetail(Request $request, string $slug, WebsiteContent $website, SmilizHtmlRenderer $renderer, SmilizPageRegistry $registry): View
    {
        return $this->renderItemDetail($request, $website, $renderer, $registry, 'service-details', function () use ($website, $slug) {
            $feature = $website->findServiceBySlug($slug);
            if (! $feature) {
                abort(404);
            }

            return [
                'service_page' => $website->resolvedServiceDetail($feature),
                'services_sidebar' => $website->servicesSidebarItems(null, $feature['slug'] ?? null),
                'pageTitle' => $website->resolvedServiceDetail($feature)['title'] ?? $feature['title'],
            ];
        }, app(WebsiteLocale::class)->pageUrl('services/'.$slug));
    }

    public function blogDetail(Request $request, string $slug, WebsiteContent $website, SmilizHtmlRenderer $renderer, SmilizPageRegistry $registry): View
    {
        return $this->renderItemDetail($request, $website, $renderer, $registry, 'blog-single-details', function () use ($website, $slug) {
            $post = $website->findBlogPostBySlug($slug);
            if (! $post) {
                abort(404);
            }

            $detail = $website->resolvedBlogDetail($post);

            return [
                'blog_page' => $detail,
                'pageTitle' => $detail['title'] ?? $post['title'],
            ];
        }, app(WebsiteLocale::class)->pageUrl('blog/'.$slug));
    }

    public function caseStudyDetail(Request $request, string $slug, WebsiteContent $website, SmilizHtmlRenderer $renderer, SmilizPageRegistry $registry): View
    {
        return $this->renderItemDetail($request, $website, $renderer, $registry, 'case-study-style-1', function () use ($website, $slug) {
            $case = $website->findCaseStudyBySlug($slug);
            if (! $case) {
                abort(404);
            }

            $detail = $website->resolvedCaseStudyDetail($case);

            return [
                'case_study_page' => $detail,
                'case_study_slug' => $slug,
                'pageTitle' => $detail['title'] ?? $case['title'],
            ];
        }, app(WebsiteLocale::class)->pageUrl('case-studies/'.$slug));
    }

    /** @param  callable(): array<string, mixed>  $contextResolver */
    private function renderItemDetail(
        Request $request,
        WebsiteContent $website,
        SmilizHtmlRenderer $renderer,
        SmilizPageRegistry $registry,
        string $pageKey,
        callable $contextResolver,
        string $canonicalUrl,
    ): View {
        if (! $this->canViewSite($request, $website)) {
            return view('website.unpublished', [
                'projectName' => Setting::projectName(),
                'logoUrl' => Setting::logoUrl(),
            ]);
        }

        $isPreview = $this->isAdminPreview($request);
        $page = $registry->find($pageKey);

        if (! $page || (! $registry->isEnabled($pageKey) && ! $isPreview)) {
            abort(404);
        }

        $context = $contextResolver();
        $rendered = $renderer->render($pageKey, $context);

        return view('website.smiliz.html-page', array_merge($this->sharedViewData($request, $website), [
            'pageHtml' => $rendered['html'],
            'pageTitle' => $context['pageTitle'] ?? $rendered['title'],
            'pageDescription' => $rendered['description'],
            'currentPageKey' => $pageKey,
            'pageMeta' => $rendered['page'],
            'hasBeforeAfter' => $rendered['has_before_after'] ?? false,
            'canonicalUrl' => $canonicalUrl,
        ]));
    }

    private function renderStaticPage(
        Request $request,
        string $pageKey,
        WebsiteContent $website,
        SmilizHtmlRenderer $renderer,
        SmilizPageRegistry $registry,
    ): View {
        $isPreview = $this->isAdminPreview($request);
        $page = $registry->find($pageKey);

        if (! $page || (! $registry->isEnabled($pageKey) && ! $isPreview)) {
            abort(404);
        }

        $rendered = $renderer->render($pageKey);

        return view('website.smiliz.html-page', array_merge($this->sharedViewData($request, $website), [
            'pageHtml' => $rendered['html'],
            'pageTitle' => $rendered['title'],
            'pageDescription' => $rendered['description'],
            'currentPageKey' => $pageKey,
            'pageMeta' => $rendered['page'],
            'hasBeforeAfter' => $rendered['has_before_after'] ?? false,
            'canonicalUrl' => $registry->pageUrl($pageKey, app(WebsiteLocale::class)->current()),
        ]));
    }

    private function legacyDetailRedirect(string $pageKey, WebsiteContent $website, SmilizPageRegistry $registry, string $locale): ?RedirectResponse
    {
        return match ($pageKey) {
            'blog-single-details' => $registry->isEnabled('blog-classic')
                ? redirect($registry->pageUrl('blog-classic', $locale), 301)
                : null,
            'case-study-style-1' => $registry->isEnabled('portfolio-grid-col-4')
                ? redirect($registry->pageUrl('portfolio-grid-col-4', $locale), 301)
                : null,
            default => null,
        };
    }

    private function canViewSite(Request $request, WebsiteContent $website): bool
    {
        $content = $website->all();

        return $content['published'] || $this->isAdminPreview($request);
    }

    private function isAdminPreview(Request $request): bool
    {
        return $request->boolean('preview')
            && auth()->check()
            && auth()->user()?->isAdmin();
    }

    /** @return array<string, mixed> */
    private function sharedViewData(Request $request, WebsiteContent $website): array
    {
        $locale = app(WebsiteLocale::class);
        $content = $website->all($locale->current());
        $loginRoute = route('login');
        $heroCtaUrl = trim((string) ($content['hero']['cta_url'] ?? ''));

        return [
            'content' => $content,
            'websiteContent' => $website,
            'showcases' => $website->publishedShowcases(),
            'portfolioItems' => $website->portfolioItems(),
            'portfolioUsesDemo' => $website->portfolioUsesDemoImages(),
            'heroImageUrl' => $website->heroImageUrl(),
            'logoUrl' => Setting::logoUrl(),
            'projectName' => Setting::projectName(),
            'isPreview' => $this->isAdminPreview($request),
            'loginUrl' => filled($heroCtaUrl) ? $heroCtaUrl : $loginRoute,
            'navMenu' => app(SmilizPageRegistry::class)->navigation($locale->current()),
            'currentPageKey' => null,
            'websiteHomeUrl' => $locale->homeUrl(),
        ];
    }

    private function resolveTemplateView(string $template): string
    {
        $views = [
            'smiliz-homepage-1' => 'website.smiliz.homepage-1',
            'smiliz-homepage-2' => 'website.smiliz.homepage-2',
            'lineup-placeholder' => 'website.home',
        ];

        $view = $views[$template] ?? $views['lineup-placeholder'];

        return view()->exists($view) ? $view : $views['lineup-placeholder'];
    }
}
