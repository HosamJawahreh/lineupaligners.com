<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait HasCaseDataZipArchive
{
    abstract protected function caseDataZipDownloadRouteName(): string;

    public function hasCaseDataZip(): bool
    {
        if (! filled($this->case_data_zip)) {
            return false;
        }

        return Storage::disk('public')->exists($this->case_data_zip);
    }

    public function caseDataZipDownloadUrl(): ?string
    {
        if (! $this->hasCaseDataZip()) {
            return null;
        }

        return route($this->caseDataZipDownloadRouteName(), [
            $this->patient,
            $this,
            'download' => 1,
        ]);
    }

    public function caseDataZipDisplayName(): string
    {
        if ($this->case_data_zip_name) {
            return basename($this->case_data_zip_name);
        }

        return 'case-data.zip';
    }

    public function caseDataZipSizeLabel(): ?string
    {
        return $this->patient?->scanSizeLabelForPath($this->case_data_zip);
    }
}
