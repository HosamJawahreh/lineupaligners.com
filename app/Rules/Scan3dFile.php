<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class Scan3dFile implements ValidationRule
{
    private const EXTENSIONS = ['stl', 'obj', 'ply'];

    public function __construct(
        private readonly int $maxKilobytes = 102400
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $ext = strtolower($value->getClientOriginalExtension() ?: '');
        if (! in_array($ext, self::EXTENSIONS, true)) {
            $fail('The :attribute must be an STL, OBJ, or PLY file.');

            return;
        }

        if ($value->getSize() > $this->maxKilobytes * 1024) {
            $fail('The :attribute may not be larger than '.round($this->maxKilobytes / 1024, 1).' MB.');
        }
    }
}
