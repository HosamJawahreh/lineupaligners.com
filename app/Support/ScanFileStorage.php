<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScanFileStorage
{
    /** @var list<string> */
    public const EXTENSIONS = ['stl', 'obj', 'ply', 'zip'];

    public static function store(UploadedFile $file, string $directory, string $namePrefix): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'stl');
        if (! in_array($ext, self::EXTENSIONS, true)) {
            $ext = 'stl';
        }

        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'scan';
        $filename = $namePrefix.'_'.$base.'.'.$ext;
        $disk = Storage::disk('public');
        $absoluteDir = $disk->path($directory);

        if (! is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $path = $file->storeAs($directory, $filename, 'public');

        if (! $path || ! $disk->exists($path)) {
            throw new \RuntimeException('Could not write the 3D file to storage.');
        }

        return $path;
    }
}
