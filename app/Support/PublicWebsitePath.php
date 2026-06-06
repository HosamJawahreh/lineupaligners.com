<?php

namespace App\Support;

use Illuminate\Http\Request;

class PublicWebsitePath
{
    /** @var list<string> */
    private const APP_PREFIXES = [
        'admin',
        'api',
        'dashboard',
        'doctors',
        'forgot-password',
        'login',
        'logout',
        'notifications',
        'patients',
        'profile',
        'sitemap.xml',
        'up',
        'website/inquiry',
    ];

    public static function isMarketingRequest(Request $request): bool
    {
        if ($request->routeIs('website.*', 'website.ar.*')) {
            return true;
        }

        $path = trim($request->path(), '/');

        if ($path === '' || $path === 'ar') {
            return true;
        }

        if (str_starts_with($path, 'ar/')) {
            $path = ltrim(substr($path, 2), '/');
        }

        $first = explode('/', $path)[0] ?? '';

        return $first !== '' && ! in_array($first, self::APP_PREFIXES, true);
    }
}
