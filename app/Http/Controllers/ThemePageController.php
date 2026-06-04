<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ThemePageController extends Controller
{
    public function show(string $slug): View
    {
        $pages = config('theme-pages', []);

        if (! isset($pages[$slug])) {
            abort(404);
        }

        $view = 'theme.pages.'.$slug;

        if (! view()->exists($view)) {
            abort(404);
        }

        return view($view);
    }
}
