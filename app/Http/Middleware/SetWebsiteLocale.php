<?php

namespace App\Http\Middleware;

use App\Services\WebsiteLocale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetWebsiteLocale
{
    public function handle(Request $request, Closure $next): Response
    {
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

        return $next($request);
    }
}
