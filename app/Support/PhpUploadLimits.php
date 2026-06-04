<?php

namespace App\Support;

class PhpUploadLimits
{
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
