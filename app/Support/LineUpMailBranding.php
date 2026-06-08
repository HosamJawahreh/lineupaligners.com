<?php

namespace App\Support;

use App\Models\Setting;
use App\Services\BrandColors;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class LineUpMailBranding
{
    /**
     * @return array{
     *     projectName: string,
     *     clinicName: string,
     *     logoUrl: string|null,
     *     websiteUrl: string,
     *     clinicEmail: string,
     *     clinicPhone: string,
     *     clinicAddress: string,
     *     primaryColor: string,
     *     secondaryColor: string
     * }
     */
    public static function data(): array
    {
        $defaults = [
            'projectName' => (string) config('app.name', 'LineUp Aligners'),
            'clinicName' => (string) config('settings.defaults.clinic_name', 'LineUp Aligners'),
            'logoUrl' => self::absoluteUrl(Setting::defaultLogoAsset()),
            'websiteUrl' => (string) config('app.url', url('/')),
            'clinicEmail' => (string) config('settings.defaults.clinic_email', ''),
            'clinicPhone' => (string) config('settings.defaults.clinic_phone', ''),
            'clinicAddress' => (string) config('settings.defaults.clinic_address', ''),
            'primaryColor' => '#1a7fd4',
            'secondaryColor' => '#09243c',
        ];

        if (! self::settingsAvailable()) {
            return $defaults;
        }

        try {
            $colors = app(BrandColors::class)->tokens();

            return [
                'projectName' => Setting::projectName(),
                'clinicName' => (string) Setting::get('clinic_name', Setting::projectName()),
                'logoUrl' => self::absoluteUrl(Setting::logoUrl()),
                'websiteUrl' => (string) config('app.url', url('/')),
                'clinicEmail' => (string) Setting::get('clinic_email', ''),
                'clinicPhone' => (string) Setting::get('clinic_phone', ''),
                'clinicAddress' => (string) Setting::get('clinic_address', ''),
                'primaryColor' => $colors['primary'] ?? $defaults['primaryColor'],
                'secondaryColor' => $colors['secondary'] ?? $defaults['secondaryColor'],
            ];
        } catch (\Throwable) {
            return $defaults;
        }
    }

    public static function applyGlobalConfig(): void
    {
        config([
            'mail.from.address' => self::fromAddress(),
            'mail.from.name' => self::fromName(),
        ]);
    }

    public static function settingsAvailable(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function fromAddress(): string
    {
        $clinicEmail = self::settingsAvailable()
            ? trim((string) Setting::get('clinic_email', ''))
            : '';

        if (filled($clinicEmail)) {
            return $clinicEmail;
        }

        return (string) config('mail.from.address', 'hello@example.com');
    }

    public static function fromName(): string
    {
        if (self::settingsAvailable()) {
            $clinicName = trim((string) Setting::get('clinic_name', ''));

            if (filled($clinicName)) {
                return $clinicName;
            }

            return Setting::projectName();
        }

        return (string) config('mail.from.name', config('app.name'));
    }

    public static function replyToAddress(): ?string
    {
        $email = self::fromAddress();

        return filled($email) && $email !== 'hello@example.com' ? $email : null;
    }

    public static function subjectPrefix(string $subject): string
    {
        return self::fromName().' — '.$subject;
    }

    public static function absoluteUrl(?string $url): ?string
    {
        if (! filled($url)) {
            return null;
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return URL::to($url);
    }
}
