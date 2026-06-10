<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CaseDataZipStorage
{
    public static function store(UploadedFile $file, string $directory): array
    {
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'case-data';
        $filename = $base.'.zip';
        $disk = Storage::disk('public');

        if ($disk->exists("{$directory}/{$filename}")) {
            $filename = $base.'_'.time().'.zip';
        }

        $path = $file->storeAs($directory, $filename, 'public');

        if (! $path || ! $disk->exists($path)) {
            throw new \RuntimeException('Could not write the case data archive to storage.');
        }

        return [
            'case_data_zip' => $path,
            'case_data_zip_name' => $file->getClientOriginalName(),
        ];
    }

    public static function replaceOnModel(Model $record, UploadedFile $file, string $directory): void
    {
        self::deleteFromModel($record);

        $record->update(self::store($file, $directory));
    }

    public static function deleteFromModel(Model $record): void
    {
        $path = $record->case_data_zip ?? null;

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
