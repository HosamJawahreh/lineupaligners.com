<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PatientCaseModification extends Model
{
    protected static function booted(): void
    {
        static::deleting(function (PatientCaseModification $modification) {
            $modification->deleteScanFiles();
        });
    }

    protected $fillable = [
        'patient_id',
        'stage_number',
        'version',
        'is_current',
        'upper_jaw_scan',
        'upper_jaw_scan_name',
        'lower_jaw_scan',
        'lower_jaw_scan_name',
        'notes',
        'requested_by',
        'treatment_plan_id',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'stage_number' => 'integer',
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

    public function hasScans(): bool
    {
        return (bool) ($this->upper_jaw_scan || $this->lower_jaw_scan);
    }

    public function scopeLabel(): string
    {
        if ($this->stage_number !== null) {
            return 'Stage '.$this->stage_number.' · Modification #'.$this->version;
        }

        return 'Modification #'.$this->version;
    }

    public function scanViewUrl(string $scan): ?string
    {
        $field = $scan === 'upper' ? 'upper_jaw_scan' : 'lower_jaw_scan';

        if (! $this->{$field} || ! Storage::disk('public')->exists($this->{$field})) {
            return null;
        }

        return route('patients.modifications.scans.download', [$this->patient, $this, $scan]);
    }

    public function scanDownloadUrl(string $scan): ?string
    {
        $field = $scan === 'upper' ? 'upper_jaw_scan' : 'lower_jaw_scan';

        if (! $this->{$field} || ! Storage::disk('public')->exists($this->{$field})) {
            return null;
        }

        return route('patients.modifications.scans.download', [
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
            'upper' => ['field' => 'upper_jaw_scan', 'name_field' => 'upper_jaw_scan_name', 'label' => 'Upper 3D model (modification)'],
            'lower' => ['field' => 'lower_jaw_scan', 'name_field' => 'lower_jaw_scan_name', 'label' => 'Lower 3D model (modification)'],
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
                'name' => $this->{$meta['name_field']} ?: $patient->scanDisplayName($this->{$field}, $field),
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
    }
}
