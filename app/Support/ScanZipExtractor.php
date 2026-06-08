<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class ScanZipExtractor
{
    private const SCAN_EXTENSIONS = ['stl', 'obj', 'ply'];

    /**
     * Extract the first 3D scan file from a ZIP archive.
     */
    public static function extractScan(UploadedFile $zip): ?UploadedFile
    {
        if (strtolower($zip->getClientOriginalExtension() ?: '') !== 'zip') {
            return null;
        }

        $archive = new ZipArchive;
        $opened = $archive->open($zip->getRealPath());

        if ($opened !== true) {
            return null;
        }

        $matchIndex = null;
        $matchName = null;

        for ($i = 0; $i < $archive->numFiles; $i++) {
            $name = $archive->getNameIndex($i);
            if (! is_string($name) || str_ends_with($name, '/')) {
                continue;
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, self::SCAN_EXTENSIONS, true)) {
                $matchIndex = $i;
                $matchName = basename($name);
                break;
            }
        }

        if ($matchIndex === null) {
            $archive->close();

            return null;
        }

        $stream = $archive->getStream($archive->getNameIndex($matchIndex));
        $archive->close();

        if (! is_resource($stream)) {
            return null;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'scanzip_');
        $out = fopen($tmp, 'w+b');

        if ($out === false) {
            fclose($stream);

            return null;
        }

        stream_copy_to_stream($stream, $out);
        fclose($stream);
        fclose($out);

        return new UploadedFile(
            $tmp,
            $matchName,
            null,
            null,
            true
        );
    }

    /**
     * @param  list<string>  $fields
     */
    public static function normalizeRequestFiles(Request $request, array $fields): void
    {
        foreach ($fields as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $file = $request->file($field);
            if (strtolower($file->getClientOriginalExtension() ?: '') !== 'zip') {
                continue;
            }

            $extracted = self::extractScan($file);

            if ($extracted === null) {
                throw ValidationException::withMessages([
                    $field => 'The ZIP archive must contain one STL, OBJ, or PLY scan file.',
                ]);
            }

            $request->files->set($field, $extracted);
        }
    }
}
