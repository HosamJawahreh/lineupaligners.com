<?php

namespace App\Models;

use App\Support\PublicStorageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever('setting.'.$key, function () use ($key, $default) {
            return static::query()->where('key', $key)->value('value') ?? $default;
        });
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = static::get($key, $default ? '1' : '0');

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function allSettings(): array
    {
        return Cache::rememberForever('settings.all', function () {
            $defaults = config('settings.defaults', []);
            $stored = static::query()->pluck('value', 'key')->all();

            return array_merge($defaults, $stored);
        });
    }

    public static function set(string $key, mixed $value): void
    {
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('setting.'.$key);
        Cache::forget('settings.all');
    }

    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::set($key, $value);
        }
    }

    public static function defaultLogoAsset(): string
    {
        $smilizLogo = 'assets/smiliz/images/logo.svg';

        if (is_file(public_path($smilizLogo))) {
            return asset($smilizLogo);
        }

        return asset('assets/images/logo.svg');
    }

    public static function logoUrl(): string
    {
        $logo = trim((string) static::get('logo', ''));
        $fallback = static::defaultLogoAsset();

        return PublicStorageUrl::url($logo, $fallback) ?? $fallback;
    }

    public static function projectName(): string
    {
        return static::get('project_name', config('app.name'));
    }

    public static function dashboardColorMode(): string
    {
        $mode = (string) static::get('dashboard_color_mode', config('settings.defaults.dashboard_color_mode', 'light'));

        return in_array($mode, ['light', 'dark'], true) ? $mode : 'light';
    }

    /** @return array{label: string, family: string, stack: string, google_url: string} */
    public static function dashboardFont(): array
    {
        $key = (string) static::get('dashboard_font', config('settings.defaults.dashboard_font', 'cairo'));
        $fonts = config('settings.fonts', []);

        return $fonts[$key] ?? $fonts['cairo'] ?? [
            'label' => 'Cairo',
            'family' => 'Cairo',
            'stack' => "'Cairo', -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, sans-serif",
            'google_url' => 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap',
        ];
    }

    public static function timezone(): string
    {
        $tz = (string) static::get('system_timezone', config('settings.defaults.system_timezone', 'UTC'));

        return in_array($tz, timezone_identifiers_list(), true) ? $tz : 'UTC';
    }

    public static function scanRequirement(): string
    {
        $value = (string) static::get('scan_requirement', config('settings.defaults.scan_requirement', 'optional'));
        $allowed = array_keys(config('settings.scan_requirements', []));

        return in_array($value, $allowed, true) ? $value : 'optional';
    }

    public static function notificationsEnabled(): bool
    {
        return static::getBool('notifications_enabled', true);
    }

    public static function notificationEmailEnabled(): bool
    {
        return static::notificationsEnabled()
            && static::getBool('notification_email_enabled', true);
    }

    public static function notificationSoundEnabled(): bool
    {
        return static::notificationsEnabled()
            && static::getBool('notification_sound_enabled', true);
    }

    /** @return array<string, array{in_app: bool, email: bool}> */
    public static function notificationTypeSettings(): array
    {
        $raw = static::get('notification_types', '');
        $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];

        if (! is_array($decoded)) {
            $decoded = [];
        }

        $result = [];
        foreach (config('lineup-notifications.types', []) as $type) {
            $stored = $decoded[$type] ?? [];
            $result[$type] = [
                'in_app' => filter_var($stored['in_app'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'email' => filter_var($stored['email'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        return $result;
    }

    public static function notificationTypeEnabled(string $type, string $channel = 'in_app'): bool
    {
        if (! static::notificationsEnabled()) {
            return false;
        }

        if ($channel === 'email' && ! static::notificationEmailEnabled()) {
            return false;
        }

        $settings = static::notificationTypeSettings();

        return $settings[$type][$channel] ?? true;
    }

    public static function setNotificationTypeSettings(array $types): void
    {
        $normalized = [];
        $allowed = config('lineup-notifications.types', []);

        foreach ($allowed as $type) {
            $row = $types[$type] ?? [];
            $normalized[$type] = [
                'in_app' => filter_var($row['in_app'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'email' => filter_var($row['email'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        static::set('notification_types', json_encode($normalized));
    }
}
