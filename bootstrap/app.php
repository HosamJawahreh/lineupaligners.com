<?php

use App\Models\Setting;
use App\Services\SmilizPageRegistry;
use App\Services\WebsiteContent;
use App\Services\WebsiteLocale;
use App\Support\PublicWebsitePath;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Hostinger / shared hosting terminates SSL at the edge and forwards HTTP internally.
        $middleware->trustProxies(at: '*');

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'website.locale' => \App\Http\Middleware\SetWebsiteLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $message = 'The uploaded files are too large for the server limit. Stop the server and restart with: php artisan serve (uses 128M limits).';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            $redirect = redirect()->back()->withInput()->with('error', $message);

            if ($request->routeIs('patients.refinements.store')) {
                return $redirect->with('open_tab', 'order-refinement');
            }

            if ($request->routeIs('patients.modifications.store')) {
                return $redirect->with('open_tab', 'modification');
            }

            return $redirect;
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || ! PublicWebsitePath::isMarketingRequest($request)) {
                return null;
            }

            $locale = app(WebsiteLocale::class);
            $locale->set($locale->detectFromRequest($request));
            app()->setLocale($locale->current());

            view()->share([
                'websiteLocale' => $locale->current(),
                'websiteDir' => $locale->dir(),
                'websiteLocaleMeta' => $locale->meta(),
                'websiteLocales' => $locale->enabled(),
                'websiteLocaleService' => $locale,
            ]);

            $website = app(WebsiteContent::class);
            $content = $website->all($locale->current());

            if (! ($content['published'] ?? false)) {
                return response()->view('website.unpublished', [
                    'projectName' => Setting::projectName(),
                    'logoUrl' => Setting::logoUrl(),
                ], 404);
            }

            return response()->view('website.errors.404', [
                'content' => $content,
                'websiteContent' => $website,
                'logoUrl' => Setting::logoUrl(),
                'projectName' => Setting::projectName(),
                'navMenu' => app(SmilizPageRegistry::class)->navigation($locale->current()),
                'currentPageKey' => null,
                'websiteHomeUrl' => $locale->homeUrl(),
                'loginUrl' => route('login'),
                'isPreview' => false,
                'showcases' => $website->publishedShowcases(),
                'portfolioItems' => $website->portfolioItems(),
                'portfolioUsesDemo' => $website->portfolioUsesDemoImages(),
                'heroImageUrl' => $website->heroImageUrl(),
            ], 404);
        });
    })->create();
