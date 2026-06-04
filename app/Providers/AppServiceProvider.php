<?php

namespace App\Providers;

use App\Console\Commands\ServeCommand;
use App\Models\Setting;
use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BaseServeCommand::class, static fn (): ServeCommand => new ServeCommand);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFour();

        View::share([
            'brandName' => Setting::projectName(),
            'faviconUrl' => asset('assets/images/favicon-32.png'),
            'faviconAppleUrl' => asset('assets/images/favicon-180.png'),
        ]);

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
            $skinMeta = config('settings.skins.'.$skin);
            $themeSkinColor = is_array($skinMeta) ? ($skinMeta['color'] ?? '#00cfd1') : '#00cfd1';

            $view->with([
                'appSettings' => $settings,
                'projectName' => $settings['project_name'] ?? config('app.name'),
                'logoUrl' => Setting::logoUrl(),
                'themeSkinColor' => $themeSkinColor,
                'bodyThemeClass' => 'theme-'.$skin,
                'bodyMenuClasses' => trim(
                    ($menuStyle === 'dark' ? 'menu_dark' : '').
                    ($menuStyle === 'image' ? ' menu_img' : '')
                ),
            ]);
        });
    }
}
