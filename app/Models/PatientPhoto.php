<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientPhoto extends Model
{
    protected $fillable = [
        'patient_id',
        'path',
        'original_name',
        'sort_order',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function url(): string
    {
        return asset('storage/'.$this->path);
    }
}
