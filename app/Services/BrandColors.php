<?php

namespace App\Services;

use App\Models\Setting;

class BrandColors
{
    /** @return array<string, string> */
    public function tokens(): array
    {
        $settings = Setting::allSettings();
        $skinKey = (string) ($settings['theme_skin'] ?? config('settings.defaults.theme_skin', 'cyan'));
        $skinMeta = config('settings.skins.'.$skinKey, []);
        $skinColor = is_array($skinMeta) ? (string) ($skinMeta['color'] ?? '#1a7fd4') : '#1a7fd4';

        $primary = $this->normalizeHex($settings['brand_primary'] ?? '')
            ?: $this->normalizeHex($skinColor)
            ?: '#1a7fd4';

        $secondary = $this->normalizeHex($settings['brand_secondary'] ?? '')
            ?: $this->normalizeHex(config('settings.defaults.brand_secondary', '#09243c'))
            ?: '#09243c';

        $primaryDark = $this->darken($primary, 14);
        $secondaryDark = $this->darken($secondary, 10);

        return [
            'primary' => $primary,
            'secondary' => $secondary,
            'primary_dark' => $primaryDark,
            'secondary_dark' => $secondaryDark,
            'primary_soft' => $this->mixWithWhite($primary, 90),
            'secondary_soft' => $this->mixWithWhite($secondary, 92),
            'primary_rgb' => $this->toRgbString($primary),
            'secondary_rgb' => $this->toRgbString($secondary),
        ];
    }

    public function inlineStyle(): string
    {
        $t = $this->tokens();

        return implode('; ', [
            '--lineup-skin: '.$t['primary'],
            '--lineup-brand: '.$t['primary'],
            '--lineup-brand-dark: '.$t['primary_dark'],
            '--lineup-brand-soft: '.$t['primary_soft'],
            '--lineup-brand-secondary: '.$t['secondary'],
            '--lineup-brand-secondary-dark: '.$t['secondary_dark'],
            '--lineup-brand-secondary-soft: '.$t['secondary_soft'],
            '--lineup-brand-primary-rgb: '.$t['primary_rgb'],
            '--lineup-brand-secondary-rgb: '.$t['secondary_rgb'],
            '--lineup-font-sans: '.Setting::dashboardFont()['stack'],
            '--lineup-font-display: var(--lineup-font-sans)',
        ]);
    }

    public function normalizeHex(?string $hex): ?string
    {
        if (! is_string($hex)) {
            return null;
        }

        $hex = trim($hex);

        if (preg_match('/^#([0-9a-fA-F]{6})$/', $hex, $matches)) {
            return '#'.strtolower($matches[1]);
        }

        if (preg_match('/^#([0-9a-fA-F]{3})$/', $hex, $matches)) {
            $short = strtolower($matches[1]);

            return '#'.$short[0].$short[0].$short[1].$short[1].$short[2].$short[2];
        }

        return null;
    }

    private function darken(string $hex, int $percent): string
    {
        [$r, $g, $b] = $this->hexToRgb($hex);
        $factor = 1 - ($percent / 100);

        return $this->rgbToHex(
            (int) max(0, min(255, round($r * $factor))),
            (int) max(0, min(255, round($g * $factor))),
            (int) max(0, min(255, round($b * $factor)))
        );
    }

    private function mixWithWhite(string $hex, int $whitePercent): string
    {
        [$r, $g, $b] = $this->hexToRgb($hex);
        $ratio = $whitePercent / 100;

        return $this->rgbToHex(
            (int) round($r + (255 - $r) * $ratio),
            (int) round($g + (255 - $g) * $ratio),
            (int) round($b + (255 - $b) * $ratio)
        );
    }

    /** @return array{0: int, 1: int, 2: int} */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($this->normalizeHex($hex) ?? '#000000', '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function rgbToHex(int $r, int $g, int $b): string
    {
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    private function toRgbString(string $hex): string
    {
        [$r, $g, $b] = $this->hexToRgb($hex);

        return $r.', '.$g.', '.$b;
    }
}
