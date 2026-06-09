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
     *     mailBrandLabel: string,
     *     primaryColor: string,
     *     secondaryColor: string
     * }
     */
    public static function data(): array
    {
        $defaults = [
            'projectName' => (string) config('settings.brand_name', config('app.name', 'Lineup Aligner')),
            'clinicName' => (string) config('settings.brand_name', config('settings.defaults.clinic_name', 'Lineup Aligner')),
            'mailBrandLabel' => (string) config('settings.brand_name', 'Lineup Aligner'),
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
                'clinicName' => Setting::clinicName(),
                'mailBrandLabel' => Setting::normalizeBrandName((string) Setting::get(
                    'mail_brand_label',
                    config('settings.brand_name', 'Lineup Aligner')
                )),
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
        $envFrom = trim((string) config('mail.from.address', ''));

        if (filled($envFrom) && filter_var($envFrom, FILTER_VALIDATE_EMAIL)) {
            return $envFrom;
        }

        $clinicEmail = self::settingsAvailable()
            ? trim((string) Setting::get('clinic_email', ''))
            : '';

        if (filled($clinicEmail) && filter_var($clinicEmail, FILTER_VALIDATE_EMAIL)) {
            return $clinicEmail;
        }

        return 'hello@example.com';
    }

    public static function fromName(): string
    {
        if (self::settingsAvailable()) {
            return Setting::clinicName();
        }

        return Setting::normalizeBrandName((string) config('mail.from.name', config('app.name')));
    }

    public static function replyToAddress(): ?string
    {
        if (! self::settingsAvailable()) {
            return null;
        }

        $clinicEmail = trim((string) Setting::get('clinic_email', ''));

        if (filled($clinicEmail) && filter_var($clinicEmail, FILTER_VALIDATE_EMAIL)) {
            return $clinicEmail;
        }

        $from = self::fromAddress();

        return $from !== 'hello@example.com' ? $from : null;
    }

    public static function clinicName(): string
    {
        return self::data()['clinicName'];
    }

    public static function subjectPrefix(string $subject): string
    {
        return self::fromName().' — '.$subject;
    }

    public static function subjectForPatient(string $patientName, string $detail): string
    {
        $patientName = trim($patientName);
        $detail = trim($detail);

        if ($patientName !== '' && stripos($detail, $patientName) === false) {
            return self::subjectPrefix($patientName.' — '.$detail);
        }

        return self::subjectPrefix($detail);
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

    public static function routeUrl(string $routeName, array $parameters = []): string
    {
        $path = route($routeName, $parameters, false);
        $base = rtrim((string) (self::data()['websiteUrl'] ?? config('app.url', url('/'))), '/');

        return $base.$path;
    }
}
