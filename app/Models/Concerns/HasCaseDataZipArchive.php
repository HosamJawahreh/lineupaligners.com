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

    /** @return list<array{label: string, name: string, url: string, size: ?string}> */
    public function zipTimelineDownloads(): array
    {
        $downloads = [];

        if ($this->hasCaseDataZip()) {
            $downloads[] = [
                'label' => 'ZIP archive',
                'name' => $this->caseDataZipDisplayName(),
                'url' => $this->caseDataZipDownloadUrl(),
                'size' => $this->caseDataZipSizeLabel(),
            ];
        }

        $patient = $this->relationLoaded('patient') ? $this->patient : null;

        foreach ([
            'upper' => ['field' => 'upper_jaw_scan', 'name_field' => 'upper_jaw_scan_name'],
            'lower' => ['field' => 'lower_jaw_scan', 'name_field' => 'lower_jaw_scan_name'],
        ] as $scan => $meta) {
            $path = $this->{$meta['field']} ?? null;

            if (! $path || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'zip') {
                continue;
            }

            if ($path === $this->case_data_zip) {
                continue;
            }

            $url = $this->scanDownloadUrl($scan);

            if (! $url) {
                continue;
            }

            $downloads[] = [
                'label' => 'ZIP archive',
                'name' => $this->{$meta['name_field']} ?: basename($path),
                'url' => $url,
                'size' => $patient?->scanSizeLabelForPath($path),
            ];
        }

        return $downloads;
    }
}
