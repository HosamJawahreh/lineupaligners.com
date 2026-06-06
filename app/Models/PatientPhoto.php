<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientPhoto extends Model
{
    protected $fillable = [
        'patient_id',
        'modification_id',
        'refinement_id',
        'path',
        'original_name',
        'sort_order',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function modification(): BelongsTo
    {
        return $this->belongsTo(PatientCaseModification::class, 'modification_id');
    }

    public function refinement(): BelongsTo
    {
        return $this->belongsTo(PatientCaseRefinement::class, 'refinement_id');
    }

    public function scopeForSetKey($query, string $setKey)
    {
        if ($setKey === 'original') {
            return $query->whereNull('modification_id')->whereNull('refinement_id');
        }

        if (str_starts_with($setKey, 'mod-')) {
            return $query->where('modification_id', (int) substr($setKey, 4));
        }

        if (str_starts_with($setKey, 'ref-')) {
            return $query->where('refinement_id', (int) substr($setKey, 4));
        }

        return $query->whereRaw('0 = 1');
    }

    public function url(): string
    {
        return asset('storage/'.$this->path);
    }

    public function downloadFilename(): string
    {
        $name = $this->original_name ?: basename($this->path);

        return preg_replace('/[^\w.\-]+/u', '_', $name) ?: 'case-photo-'.$this->id.'.jpg';
    }
}
