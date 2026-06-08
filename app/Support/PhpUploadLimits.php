<?php

namespace App\Support;

use Illuminate\Http\Request;

class PhpUploadLimits
{
    /**
     * True when PHP could not parse the POST body (typically post_max_size exceeded).
     * Empty optional forms still include _token when parsed successfully.
     */
    public static function requestPayloadUnparsed(Request $request): bool
    {
        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);

        if ($contentLength < 1) {
            return false;
        }

        if ($request->has('_token')) {
            return false;
        }

        $postMax = self::postMaxBytes();

        return $postMax > 0 && $contentLength > $postMax;
    }

    public static function uploadTooLargeMessage(): string
    {
        return 'Upload too large for PHP limits. Restart with: php -c php.ini artisan serve (128M), or upload one smaller file at a time.';
    }
    public static function postMaxBytes(): int
    {
        return self::parseIniSize(ini_get('post_max_size'));
    }

    public static function uploadMaxBytes(): int
    {
        return self::parseIniSize(ini_get('upload_max_filesize'));
    }

    /** Minimum recommended for 3D scan uploads (100 MB app limit). */
    public static function isAdequateForScans(): bool
    {
        return self::postMaxBytes() >= 100 * 1024 * 1024
            && self::uploadMaxBytes() >= 100 * 1024 * 1024;
    }

    public static function humanSummary(): string
    {
        return 'post_max_size='.ini_get('post_max_size').', upload_max_filesize='.ini_get('upload_max_filesize');
    }

    public static function parseIniSize(string|false $value): int
    {
        if ($value === false || $value === '') {
            return 0;
        }

        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => (int) $value,
        };
    }
}
