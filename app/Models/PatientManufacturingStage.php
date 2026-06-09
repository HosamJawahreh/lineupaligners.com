<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientManufacturingStage extends Model
{
    protected $fillable = [
        'patient_id',
        'refinement_id',
        'stage_number',
        'manufactured_step_from',
        'manufactured_step_to',
        'manufactured_at',
        'manufactured_by',
    ];

    protected function casts(): array
    {
        return [
            'stage_number' => 'integer',
            'manufactured_step_from' => 'integer',
            'manufactured_step_to' => 'integer',
            'manufactured_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function refinement(): BelongsTo
    {
        return $this->belongsTo(PatientCaseRefinement::class, 'refinement_id');
    }

    public function manufacturedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manufactured_by');
    }

    public function stepRangeLabel(): string
    {
        if ($this->manufactured_step_from === $this->manufactured_step_to) {
            return 'Step '.$this->manufactured_step_from;
        }

        return 'Steps '.$this->manufactured_step_from.'–'.$this->manufactured_step_to;
    }
}
