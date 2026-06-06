<?php

namespace App\Providers;

use App\Console\Commands\ServeCommand;
use App\Models\Setting;
use App\Services\BrandColors;
use App\Services\WebsiteLocale;
use Carbon\Carbon;
use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BaseServeCommand::class, static fn (): ServeCommand => new ServeCommand);
        $this->app->singleton(WebsiteLocale::class);
        $this->app->singleton(BrandColors::class);
        $this->app->instance('website.locale', config('website-locales.default', 'en'));
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            config([
                'session.secure' => true,
                'session.same_site' => 'lax',
            ]);
        }

        try {
            if (Schema::hasTable('settings')) {
                $timezone = Setting::timezone();
                config(['app.timezone' => $timezone]);
                date_default_timezone_set($timezone);
            }
        } catch (\Throwable) {
            // Database may be unavailable during install or migrations.
        }
        Date::use(Carbon::class);

        Paginator::useBootstrapFour();

        View::share([
            'brandName' => Setting::projectName(),
            'faviconUrl' => asset('assets/images/favicon-32.png'),
            'faviconAppleUrl' => asset('assets/images/favicon-180.png'),
        ]);

        try {
            if (Schema::hasTable('settings')) {
                View::share('brandColors', app(BrandColors::class)->tokens());
            }
        } catch (\Throwable) {
            // Database may be unavailable during install.
        }

        View::composer([
            'layouts.app',
            'layouts.partials.theme-shell',
            'layouts.partials.lineup-topbar',
            'layouts.partials.lineup-sidebar',
        ], function ($view): void {
            if (! Auth::check()) {
                return;
            }

            $settings = Setting::allSettings();
            $skin = $settings['theme_skin'] ?? 'cyan';
            $menuStyle = $settings['left_menu_style'] ?? 'light';
            $colorMode = Setting::dashboardColorMode();
            $brandColors = app(BrandColors::class);
            $tokens = $brandColors->tokens();
            $dashboardFont = Setting::dashboardFont();

            if ($colorMode === 'dark' && $menuStyle !== 'image') {
                $menuStyle = 'dark';
            }

            $view->with([
                'appSettings' => $settings,
                'projectName' => $settings['project_name'] ?? config('app.name'),
                'logoUrl' => Setting::logoUrl(),
                'themeSkinColor' => $tokens['primary'],
                'brandColors' => $tokens,
                'brandInlineStyle' => $brandColors->inlineStyle(),
                'dashboardFont' => $dashboardFont,
                'dashboardFontStack' => $dashboardFont['stack'],
                'dashboardFontUrl' => $dashboardFont['google_url'],
                'dashboardColorMode' => $colorMode,
                'bodyColorClass' => 'lineup-color-'.$colorMode,
                'bodyThemeClass' => 'theme-'.$skin,
                'bodyMenuClasses' => trim(
                    ($menuStyle === 'dark' ? 'menu_dark' : '').
                    ($menuStyle === 'image' ? ' menu_img' : '')
                ),
            ]);
        });
    }
}
