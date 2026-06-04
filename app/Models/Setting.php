<?php

namespace App\Models;

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

    public static function logoUrl(): string
    {
        $logo = static::get('logo');

        if ($logo && Storage::disk('public')->exists($logo)) {
            return asset('storage/'.$logo);
        }

        return asset('assets/images/logo.svg');
    }

    public static function projectName(): string
    {
        return static::get('project_name', config('app.name'));
    }
}
