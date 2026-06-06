<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class WebsiteLocale
{
    private string $locale;

    public function __construct(?string $locale = null)
    {
        $this->locale = $this->normalize($locale ?? config('website-locales.default', 'en'));
    }

    public function current(): string
    {
        return $this->locale;
    }

    public function set(string $locale): void
    {
        $this->locale = $this->normalize($locale);
        app()->instance('website.locale', $this->locale);
    }

    public function meta(?string $locale = null): array
    {
        $locale = $this->normalize($locale ?? $this->locale);

        return config('website-locales.locales.'.$locale, config('website-locales.locales.en'));
    }

    public function dir(?string $locale = null): string
    {
        return $this->meta($locale)['dir'] ?? 'ltr';
    }

    public function isRtl(?string $locale = null): bool
    {
        return $this->dir($locale) === 'rtl';
    }

    /** @return array<string, array<string, mixed>> */
    public function enabled(): array
    {
        $enabled = json_decode((string) \App\Models\Setting::get('website_enabled_locales'), true);

        if (! is_array($enabled) || $enabled === []) {
            $enabled = array_keys(config('website-locales.locales', []));
        }

        return collect(config('website-locales.locales', []))
            ->only($enabled)
            ->all();
    }

    public function isEnabled(string $locale): bool
    {
        return array_key_exists($this->normalize($locale), $this->enabled());
    }

    public function prefix(?string $locale = null): string
    {
        $prefix = $this->meta($locale)['url_prefix'] ?? '';

        return $prefix === '' ? '' : trim($prefix, '/');
    }

    public function homeUrl(?string $locale = null): string
    {
        $locale = $this->normalize($locale ?? $this->locale);
        $prefix = $this->prefix($locale);

        return $prefix === '' ? url('/') : url('/'.$prefix);
    }

    public function pagePath(string $path, ?string $locale = null): string
    {
        $path = '/'.trim($path, '/');
        $prefix = $this->prefix($locale ?? $this->locale);

        return $prefix === '' ? $path : '/'.$prefix.$path;
    }

    public function pageUrl(string $path, ?string $locale = null): string
    {
        return url($this->pagePath($path, $locale));
    }

    public function routeName(string $baseName, ?string $locale = null): string
    {
        $locale = $this->normalize($locale ?? $this->locale);

        if ($locale === config('website-locales.default', 'en')) {
            return $baseName;
        }

        $parts = explode('.', $baseName, 2);

        return $parts[0].'.'.$locale.'.'.($parts[1] ?? '');
    }

    public function switchUrl(string $targetLocale, ?Request $request = null): string
    {
        $request ??= request();
        $targetLocale = $this->normalize($targetLocale);
        $path = '/'.ltrim($request->path(), '/');

        if ($this->prefix() !== '' && str_starts_with($path, '/'.$this->prefix())) {
            $path = '/'.ltrim(substr($path, strlen($this->prefix()) + 1), '/');
        }

        if ($path === '/') {
            return $this->homeUrl($targetLocale);
        }

        $targetPrefix = $this->prefix($targetLocale);
        $localized = $targetPrefix === '' ? $path : '/'.$targetPrefix.$path;

        $query = $request->getQueryString();

        return url($localized).($query ? '?'.$query : '');
    }

    public function detectFromRequest(Request $request): string
    {
        if ($request->route('locale')) {
            return $this->normalize((string) $request->route('locale'));
        }

        $segment = $request->segment(1);

        if ($segment && $this->isEnabled($segment) && $segment !== config('website-locales.default', 'en')) {
            return $this->normalize($segment);
        }

        return config('website-locales.default', 'en');
    }

    public function normalize(string $locale): string
    {
        $locale = strtolower(trim($locale));
        $locales = config('website-locales.locales', []);

        return array_key_exists($locale, $locales)
            ? $locale
            : config('website-locales.default', 'en');
    }
}
