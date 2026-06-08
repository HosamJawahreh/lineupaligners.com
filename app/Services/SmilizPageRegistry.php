<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Str;

class SmilizPageRegistry
{
    public function sourceDirectory(): string
    {
        return base_path(config('smiliz-pages.source_path', 'Smiliz HTML Files'));
    }

    /** @return array<string, array<string, mixed>> */
    public function catalog(): array
    {
        $meta = config('smiliz-pages.page_meta', []);
        $homepageKey = config('smiliz-pages.homepage_key', 'homepage-2');
        $pages = [];

        foreach ($meta as $key => $pageMeta) {
            if ($key === $homepageKey || $this->isExcluded($key)) {
                continue;
            }

            $defaults = [
                'key' => $key,
                'file' => $key.'.html',
                'label' => Str::headline(str_replace('-', ' ', $key)),
                'path' => $key,
                'group' => $this->guessGroup($key),
                'default_enabled' => true,
                'default_in_nav' => false,
            ];

            $pages[$key] = array_merge($defaults, is_array($pageMeta) ? $pageMeta : []);
        }

        foreach (glob($this->sourceDirectory().'/*.html') ?: [] as $file) {
            $key = basename($file, '.html');

            if ($key === $homepageKey || $this->isExcluded($key) || isset($pages[$key])) {
                continue;
            }

            $defaults = [
                'key' => $key,
                'file' => basename($file),
                'label' => Str::headline(str_replace('-', ' ', $key)),
                'path' => $key,
                'group' => $this->guessGroup($key),
                'default_enabled' => true,
                'default_in_nav' => false,
            ];

            $pages[$key] = array_merge($defaults, $meta[$key] ?? []);
        }

        uasort($pages, fn (array $a, array $b) => strcmp($a['label'], $b['label']));

        return $pages;
    }

    public function find(string $key): ?array
    {
        return $this->catalog()[$key] ?? null;
    }

    public function findByPath(string $path): ?array
    {
        $path = trim($path, '/');

        foreach ($this->catalog() as $page) {
            if (trim($page['path'], '/') === $path) {
                return $page;
            }
        }

        return null;
    }

    /** @return array<string, array<string, mixed>> */
    public function resolvedSettings(): array
    {
        $stored = $this->applySettingAliases($this->decodeSettings(Setting::get('website_pages')));
        $resolved = [];

        foreach ($this->catalog() as $key => $page) {
            $saved = $stored[$key] ?? [];
            $resolved[$key] = array_merge($page, [
                'enabled' => array_key_exists('enabled', $saved)
                    ? (bool) $saved['enabled']
                    : (bool) $page['default_enabled'],
                'in_nav' => array_key_exists('in_nav', $saved)
                    ? (bool) $saved['in_nav']
                    : (bool) $page['default_in_nav'],
                'nav_label' => filled($saved['nav_label'] ?? null)
                    ? $saved['nav_label']
                    : $page['label'],
                'nav_label_ar' => filled($saved['nav_label_ar'] ?? null)
                    ? $saved['nav_label_ar']
                    : (config('smiliz-pages-i18n-ar.nav_labels.'.$key) ?? ''),
            ]);
        }

        return $resolved;
    }

    /** @return array{order: array<int, string>, children: array<string, array<int, string>>, labels: array<string, string>} */
    public function mainMenuConfig(): array
    {
        $decoded = $this->decodeSettings(Setting::get('website_main_menu'));

        return [
            'order' => array_values(array_filter($decoded['order'] ?? [])),
            'children' => is_array($decoded['children'] ?? null) ? $decoded['children'] : [],
            'labels' => is_array($decoded['labels'] ?? null) ? $decoded['labels'] : [],
            'labels_ar' => is_array($decoded['labels_ar'] ?? null) ? $decoded['labels_ar'] : [],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function mainMenuAdminEntries(): array
    {
        $settings = $this->resolvedSettings();
        $groups = config('smiliz-pages.groups', []);
        $menuConfig = $this->mainMenuConfig();
        $order = $this->effectiveMenuOrder($menuConfig['order'], $groups, $settings);
        $entries = [];

        foreach ($order as $groupKey) {
            if (in_array($groupKey, ['home', 'other'], true)) {
                continue;
            }

            $groupPages = collect($settings)
                ->filter(fn (array $page) => $page['group'] === $groupKey)
                ->values();

            if ($groupPages->isEmpty()) {
                continue;
            }

            $sorted = $this->sortPagesByOrder($groupPages, $menuConfig['children'][$groupKey] ?? []);
            $visibleInNav = $sorted->filter(fn (array $page) => $page['enabled'] && $page['in_nav']);

            $entries[] = [
                'group' => $groupKey,
                'label' => $menuConfig['labels'][$groupKey] ?? $groups[$groupKey] ?? $groupKey,
                'custom_label' => $menuConfig['labels'][$groupKey] ?? '',
                'custom_label_ar' => $menuConfig['labels_ar'][$groupKey] ?? '',
                'label_ar' => $menuConfig['labels_ar'][$groupKey]
                    ?? config('smiliz-pages-i18n-ar.groups.'.$groupKey)
                    ?? $groups[$groupKey]
                    ?? $groupKey,
                'is_dropdown' => $visibleInNav->count() > 1,
                'visible_count' => $visibleInNav->count(),
                'pages' => $sorted->map(fn (array $page) => [
                    'key' => $page['key'],
                    'label' => $page['label'],
                    'path' => $page['path'],
                    'nav_label' => $page['nav_label'],
                    'nav_label_ar' => $page['nav_label_ar'] ?? '',
                    'enabled' => $page['enabled'],
                    'in_nav' => $page['in_nav'],
                ])->values()->all(),
            ];
        }

        return $entries;
    }

    /** @return array<int, array<string, mixed>> */
    public function navigation(?string $locale = null): array
    {
        $groups = config('smiliz-pages.groups', []);
        $locale = $locale ?? app(WebsiteLocale::class)->current();
        $menuConfig = $this->mainMenuConfig();
        $resolved = $this->resolvedSettings();
        $groupChildren = [];

        foreach ($groups as $groupKey => $groupLabel) {
            if ($groupKey === 'home') {
                continue;
            }

            $pagesInGroup = collect($resolved)
                ->filter(fn (array $page) => $page['group'] === $groupKey && $page['enabled'] && $page['in_nav']);

            $sorted = $this->sortPagesByOrder($pagesInGroup, $menuConfig['children'][$groupKey] ?? []);

            $children = $sorted
                ->map(fn (array $page) => [
                    'key' => $page['key'],
                    'label' => $this->localizedNavLabel($page, $locale),
                    'url' => $this->pageUrl($page['key'], $locale),
                    'icon' => $this->pageNavIcon($page['key']),
                ])
                ->values()
                ->all();

            if ($children !== []) {
                $groupChildren[$groupKey] = $children;
            }
        }

        $order = $this->effectiveMenuOrder($menuConfig['order'], $groups, $resolved);
        $menu = [];

        foreach ($order as $groupKey) {
            if (! isset($groupChildren[$groupKey])) {
                continue;
            }

            $children = $groupChildren[$groupKey];
            $groupLabel = $locale === 'ar'
                ? ($menuConfig['labels_ar'][$groupKey]
                    ?? $this->localizedGroupLabel($groupKey, $locale)
                    ?? $groups[$groupKey]
                    ?? $groupKey)
                : ($menuConfig['labels'][$groupKey] ?? $groups[$groupKey] ?? $groupKey);

            $menu[] = [
                'group' => $groupKey,
                'label' => $groupLabel,
                'icon' => $this->groupNavIcon($groupKey),
                'children' => $children,
                'url' => count($children) === 1 ? $children[0]['url'] : null,
            ];
        }

        return $menu;
    }

    public function saveMainMenu(array $input): void
    {
        $order = array_values(array_filter($input['order'] ?? []));
        $children = [];

        foreach ($input['children'] ?? [] as $group => $keys) {
            if (! is_array($keys)) {
                continue;
            }

            $children[$group] = array_values(array_filter($keys));
        }

        $labels = [];

        foreach ($input['labels'] ?? [] as $group => $label) {
            $label = trim((string) $label);

            if ($label !== '') {
                $labels[$group] = $label;
            }
        }

        $labelsAr = [];

        foreach ($input['labels_ar'] ?? [] as $group => $label) {
            $label = trim((string) $label);

            if ($label !== '') {
                $labelsAr[$group] = $label;
            }
        }

        Setting::set('website_main_menu', json_encode([
            'order' => $order,
            'children' => $children,
            'labels' => $labels,
            'labels_ar' => $labelsAr,
        ], JSON_UNESCAPED_UNICODE));

        $pagesInput = $input['pages'] ?? [];
        $resolved = $this->resolvedSettings();
        $out = [];

        foreach ($this->catalog() as $key => $page) {
            $row = $pagesInput[$key] ?? null;
            $current = $resolved[$key] ?? [];

            if (is_array($row)) {
                $out[$key] = [
                    'enabled' => array_key_exists('enabled', $row)
                        ? ! empty($row['enabled'])
                        : (bool) ($current['enabled'] ?? true),
                    'in_nav' => ! empty($row['in_nav']),
                    'nav_label' => trim($row['nav_label'] ?? '') ?: ($current['nav_label'] ?? $page['label']),
                    'nav_label_ar' => trim($row['nav_label_ar'] ?? '') ?: ($current['nav_label_ar'] ?? ''),
                ];
            } else {
                $out[$key] = [
                    'enabled' => (bool) ($current['enabled'] ?? true),
                    'in_nav' => (bool) ($current['in_nav'] ?? false),
                    'nav_label' => $current['nav_label'] ?? $page['label'],
                    'nav_label_ar' => $current['nav_label_ar'] ?? '',
                ];
            }
        }

        Setting::set('website_pages', json_encode($out, JSON_UNESCAPED_UNICODE));
    }

    public function pageUrl(string $key, ?string $locale = null): string
    {
        $page = $this->find($key);

        if (! $page) {
            return app(WebsiteLocale::class)->homeUrl($locale);
        }

        return app(WebsiteLocale::class)->pageUrl($page['path'], $locale);
    }

    public function isEnabled(string $key): bool
    {
        return (bool) ($this->resolvedSettings()[$key]['enabled'] ?? false);
    }

    public function homeNavIcon(): string
    {
        return (string) config('smiliz-pages.nav_icons.home', 'ti-home');
    }

    public function groupNavIcon(string $groupKey): string
    {
        $icons = config('smiliz-pages.nav_icons.groups', []);

        return (string) ($icons[$groupKey] ?? 'pbmit-base-icon-right-arrow');
    }

    public function pageNavIcon(string $key): string
    {
        $icons = config('smiliz-pages.nav_icons.pages', []);

        if (filled($icons[$key] ?? null)) {
            return (string) $icons[$key];
        }

        $page = $this->find($key);
        $group = is_array($page) ? ($page['group'] ?? $this->guessGroup($key)) : $this->guessGroup($key);

        return $this->groupNavIcon($group);
    }

    public function saveSettings(array $input): void
    {
        $out = [];

        foreach ($this->catalog() as $key => $page) {
            $row = $input[$key] ?? [];
            $out[$key] = [
                'enabled' => ! empty($row['enabled']),
                'in_nav' => ! empty($row['in_nav']),
                'nav_label' => trim($row['nav_label'] ?? '') ?: $page['label'],
                'nav_label_ar' => trim($row['nav_label_ar'] ?? ''),
            ];
        }

        Setting::set('website_pages', json_encode($out, JSON_UNESCAPED_UNICODE));
    }

    /** @return array{total: int, translated: int, percent: int} */
    public function arabicNavCoverage(): array
    {
        $pages = collect($this->resolvedSettings())
            ->filter(fn (array $page) => $page['enabled'] && $page['in_nav']);

        $total = $pages->count();
        $translated = $pages->filter(function (array $page) {
            if (filled($page['nav_label_ar'] ?? '')) {
                return true;
            }

            return filled(config('smiliz-pages-i18n-ar.nav_labels.'.$page['key']) ?? '');
        })->count();

        return [
            'total' => $total,
            'translated' => $translated,
            'percent' => $total > 0 ? (int) round(($translated / $total) * 100) : 100,
        ];
    }

    /** @return array<string, string> */
    public function htmlLinkMap(): array
    {
        $map = [
            'index.html' => url('/'),
            'homepage-2.html' => url('/'),
        ];

        foreach ($this->catalog() as $key => $page) {
            $map[$page['file']] = $this->pageUrl($key);
        }

        return array_merge($map, $this->retiredHtmlLinkAliases());
    }

    /** @return array<string, string> */
    private function retiredHtmlLinkAliases(): array
    {
        $caseStudiesUrl = $this->find('portfolio-grid-col-4')
            ? $this->pageUrl('portfolio-grid-col-4')
            : url('/case-studies');
        $caseDetailUrl = $this->find('case-study-style-1')
            ? $this->pageUrl('case-study-style-1')
            : url('/case-studies/detail');
        $blogUrl = $this->find('blog-classic')
            ? $this->pageUrl('blog-classic')
            : url('/blog');
        $blogArticleUrl = $this->find('blog-single-details')
            ? $this->pageUrl('blog-single-details')
            : url('/blog/article');

        $aliases = [];

        foreach (glob($this->sourceDirectory().'/*.html') ?: [] as $file) {
            $key = basename($file, '.html');
            $fileName = basename($file);

            if (! $this->isExcluded($key)) {
                continue;
            }

            $aliases[$fileName] = match (true) {
                str_starts_with($key, 'portfolio') => $caseStudiesUrl,
                $key === 'case-study-style-2' => $caseDetailUrl,
                str_starts_with($key, 'blog') => str_contains($key, 'single') ? $blogArticleUrl : $blogUrl,
                default => url('/'),
            };
        }

        return $aliases;
    }

    /** @param array<string, array<string, mixed>> $stored */
    private function applySettingAliases(array $stored): array
    {
        foreach (config('smiliz-pages.page_setting_aliases', []) as $from => $to) {
            if (! array_key_exists($to, $stored) && array_key_exists($from, $stored)) {
                $stored[$to] = $stored[$from];
            }
        }

        return $stored;
    }

    private function isExcluded(string $key): bool
    {
        foreach (config('smiliz-pages.excluded_page_patterns', []) as $pattern) {
            if (@preg_match($pattern, $key) === 1) {
                return true;
            }
        }

        return false;
    }

    private function guessGroup(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'blog') => 'blog',
            str_starts_with($key, 'portfolio') || str_starts_with($key, 'case-study') => 'case_study',
            str_starts_with($key, 'contact') => 'contact',
            str_starts_with($key, 'service') => 'services',
            $key === 'faq' => 'faq',
            in_array($key, ['about-us', 'our-history', 'our-dentist', 'dentist-profile', 'appointment'], true) => 'about',
            $key === 'index' => 'home',
            default => 'other',
        };
    }

    private function decodeSettings(?string $json): array
    {
        if (! $json) {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    /** @param  array<int, string>  $savedOrder
     * @param  array<string, string>  $groups
     * @param  array<string, array<string, mixed>>  $settings
     * @return array<int, string>
     */
    private function effectiveMenuOrder(array $savedOrder, array $groups, array $settings): array
    {
        $defaultOrder = array_keys($groups);
        $order = $savedOrder !== [] ? $savedOrder : $defaultOrder;

        $groupsWithPages = collect($settings)
            ->filter(fn (array $page) => ! in_array($page['group'], ['home', 'other'], true))
            ->pluck('group')
            ->unique()
            ->values()
            ->all();

        foreach ($groupsWithPages as $groupKey) {
            if (! in_array($groupKey, $order, true)) {
                $order[] = $groupKey;
            }
        }

        return array_values(array_unique($order));
    }

    /** @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $pages
     * @param  array<int, string>  $order
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function sortPagesByOrder($pages, array $order)
    {
        if ($order === []) {
            return $pages->sortBy(fn (array $page) => $page['label'])->values();
        }

        return $pages
            ->sortBy(function (array $page) use ($order) {
                $index = array_search($page['key'], $order, true);

                return $index === false ? 9999 : $index;
            })
            ->values();
    }

    private function localizedNavLabel(array $page, ?string $locale): string
    {
        if ($locale === 'ar') {
            if (filled($page['nav_label_ar'] ?? null)) {
                return (string) $page['nav_label_ar'];
            }

            $labels = config('smiliz-pages-i18n-ar.nav_labels', []);

            if (filled($labels[$page['key']] ?? null)) {
                return $labels[$page['key']];
            }
        }

        return $page['nav_label'];
    }

    private function localizedGroupLabel(string $groupKey, ?string $locale): ?string
    {
        if ($locale !== 'ar') {
            return null;
        }

        $labels = config('smiliz-pages-i18n-ar.groups', []);

        return filled($labels[$groupKey] ?? null) ? $labels[$groupKey] : null;
    }
}
