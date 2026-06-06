<?php

use App\Http\Controllers\PublicWebsiteController;
use App\Services\SmilizPageRegistry;
use Illuminate\Support\Facades\Route;

return function (string $routePrefix = '', string $namePrefix = 'website.page.', ?string $locale = null): void {
    $registry = app(SmilizPageRegistry::class);
    $pathPrefix = $routePrefix === '' ? '' : trim($routePrefix, '/');

    foreach ($registry->catalog() as $key => $page) {
        $path = $page['path'];

        if ($pathPrefix !== '') {
            $path = $pathPrefix.'/'.ltrim($path, '/');
        }

        $route = Route::get($path, [PublicWebsiteController::class, 'page'])
            ->defaults('pageKey', $key)
            ->name($namePrefix.$key);

        if ($locale) {
            $route->defaults('locale', $locale);
        }
    }
};
