<?php

namespace App\Providers;

use App\Support\LineUpMailBranding;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MailBrandingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->resolving(Markdown::class, function (Markdown $markdown): void {
            $markdown->theme('lineup');
        });

        if (LineUpMailBranding::settingsAvailable()) {
            LineUpMailBranding::applyGlobalConfig();
        }

        View::composer([
            'vendor.mail.html.*',
            'vendor.mail.text.*',
            'mail.*',
        ], function ($view): void {
            $view->with('lineupMail', LineUpMailBranding::data());
        });
    }
}
