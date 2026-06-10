<?php

namespace App\Models;

use App\Models\Concerns\HasCaseDataZipArchive;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class PatientCaseRefinement extends Model
{
    use HasCaseDataZipArchive;
    protected static function booted(): void
    {
        static::deleting(function (PatientCaseRefinement $refinement) {
            $refinement->deleteScanFiles();
            $refinement->deletePhotos();
        });
    }

    protected $fillable = [
        'patient_id',
        'version',
        'is_current',
        'upper_jaw_scan',
        'upper_jaw_scan_name',
        'lower_jaw_scan',
        'lower_jaw_scan_name',
        'case_data_zip',
        'case_data_zip_name',
        'notes',
        'requested_by',
        'treatment_plan_id',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(PatientTreatmentPlan::class, 'treatment_plan_id');
    }

    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(PatientTreatmentPlan::class, 'refinement_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PatientPhoto::class, 'refinement_id')->orderBy('sort_order');
    }

    protected function caseDataZipDownloadRouteName(): string
    {
        return 'patients.refinements.case-data-zip.download';
    }

    public function hasScans(): bool
    {
        return (bool) ($this->upper_jaw_scan || $this->lower_jaw_scan || $this->hasCaseDataZip());
    }

    /** @return list<array{label: string, name: string, url: string, size: ?string}> */
    public function timelineDownloads(): array
    {
        $downloads = [];

        if ($this->hasCaseDataZip()) {
            $downloads[] = [
                'label' => 'Case data archive',
                'name' => $this->caseDataZipDisplayName(),
                'url' => $this->caseDataZipDownloadUrl(),
                'size' => $this->caseDataZipSizeLabel(),
            ];
        }

        foreach ($this->caseScanFiles() as $file) {
            $downloads[] = [
                'label' => $file['label'],
                'name' => $file['name'],
                'url' => $file['download_url'],
                'size' => $file['size'],
            ];
        }

        return $downloads;
    }

    public function statusLabel(): string
    {
        $plan = $this->displayTreatmentPlan();

        if ($plan?->isApproved()) {
            return 'Awaiting manufacture';
        }

        if ($this->is_current || $plan !== null) {
            return $plan !== null ? 'Awaiting doctor review' : 'Awaiting treatment plan';
        }

        return 'Completed';
    }

    /** Latest treatment plan uploaded for this refinement cycle (never the pre-refinement anchor plan). */
    public function displayTreatmentPlan(): ?PatientTreatmentPlan
    {
        $plans = $this->treatmentPlans()
            ->orderByDesc('version')
            ->orderByDesc('id')
            ->get();

        if ($plans->isEmpty()) {
            return null;
        }

        return $plans->firstWhere('review_status', PatientTreatmentPlan::STATUS_APPROVED)
            ?? $plans->firstWhere('is_current', true)
            ?? $plans->first();
    }

    public function scopeLabel(): string
    {
        return 'Refinement #'.$this->version;
    }

    public function scanViewUrl(string $scan): ?string
    {
        $field = $scan === 'upper' ? 'upper_jaw_scan' : 'lower_jaw_scan';

        if (! $this->{$field} || ! Storage::disk('public')->exists($this->{$field})) {
            return null;
        }

        return route('patients.refinements.scans.download', [$this->patient, $this, $scan]);
    }

    public function scanDownloadUrl(string $scan): ?string
    {
        $field = $scan === 'upper' ? 'upper_jaw_scan' : 'lower_jaw_scan';

        if (! $this->{$field} || ! Storage::disk('public')->exists($this->{$field})) {
            return null;
        }

        return route('patients.refinements.scans.download', [
            $this->patient,
            $this,
            $scan,
            'download' => 1,
        ]);
    }

    /** @return list<array{id: string, label: string, name: string, ext: string, size: ?string, view_url: string, download_url: string}> */
    public function caseScanFiles(): array
    {
        $files = [];
        $patient = $this->patient;

        foreach ([
            'upper' => ['field' => 'upper_jaw_scan', 'name_field' => 'upper_jaw_scan_name', 'label' => 'Upper 3D model (refinement)'],
            'lower' => ['field' => 'lower_jaw_scan', 'name_field' => 'lower_jaw_scan_name', 'label' => 'Lower 3D model (refinement)'],
        ] as $id => $meta) {
            $field = $meta['field'];

            if (! $this->{$field} || ! Storage::disk('public')->exists($this->{$field})) {
                continue;
            }

            $viewUrl = $this->scanViewUrl($id);
            $downloadUrl = $this->scanDownloadUrl($id);

            if (! $viewUrl || ! $downloadUrl) {
                continue;
            }

            $files[] = [
                'id' => $id,
                'label' => $meta['label'],
                'name' => $this->{$meta['name_field']} ?: basename($this->{$field}),
                'ext' => strtoupper(pathinfo($this->{$field}, PATHINFO_EXTENSION) ?: 'stl'),
                'size' => $patient->scanSizeLabelForPath($this->{$field}),
                'view_url' => $viewUrl,
                'download_url' => $downloadUrl,
            ];
        }

        return $files;
    }

    public function deleteScanFiles(): void
    {
        foreach (['upper_jaw_scan', 'lower_jaw_scan'] as $field) {
            $path = $this->{$field};
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        if ($this->case_data_zip && Storage::disk('public')->exists($this->case_data_zip)) {
            Storage::disk('public')->delete($this->case_data_zip);
        }
    }

    public function deletePhotos(): void
    {
        foreach ($this->photos as $photo) {
            if ($photo->path && Storage::disk('public')->exists($photo->path)) {
                Storage::disk('public')->delete($photo->path);
            }
        }
        $this->photos()->delete();
    }
}
