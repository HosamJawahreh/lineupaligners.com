<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class PublicStorageUrl
{
    /**
     * Build a browser-safe URL for a file on the public disk.
     * Returns $fallback when the file is missing or public/storage is not linked.
     */
    public static function url(?string $path, ?string $fallback = null): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return $fallback;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'assets/')) {
            return is_file(public_path($path)) ? asset($path) : ($fallback ?? asset($path));
        }

        if (! Storage::disk('public')->exists($path)) {
            return $fallback;
        }

        $publicRelative = 'storage/'.$path;

        if (is_file(public_path($publicRelative))) {
            return asset($publicRelative);
        }

        return $fallback;
    }

    public static function isPubliclyAccessible(?string $path): bool
    {
        $path = trim((string) $path);

        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            return false;
        }

        return is_file(public_path('storage/'.$path));
    }
}
