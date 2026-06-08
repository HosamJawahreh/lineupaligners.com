<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientTreatmentPlan extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'patient_id',
        'refinement_id',
        'stage_number',
        'step_from',
        'step_to',
        'plan_url',
        'review_status',
        'review_comment',
        'reviewed_by',
        'reviewed_at',
        'uploaded_by',
        'version',
        'is_current',
        'manufactured_at',
        'manufactured_by',
        'manufactured_step_from',
        'manufactured_step_to',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'manufactured_at' => 'datetime',
            'is_current' => 'boolean',
            'stage_number' => 'integer',
            'step_from' => 'integer',
            'step_to' => 'integer',
            'manufactured_step_from' => 'integer',
            'manufactured_step_to' => 'integer',
            'version' => 'integer',
        ];
    }

    public function manufacturedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manufactured_by');
    }

    public function isManufactured(): bool
    {
        return $this->manufactured_at !== null;
    }

    public function manufacturedStepRangeLabel(): string
    {
        if ($this->manufactured_step_from === null || $this->manufactured_step_to === null) {
            return '';
        }

        if ($this->manufactured_step_from === $this->manufactured_step_to) {
            return 'Step '.$this->manufactured_step_from;
        }

        return 'Steps '.$this->manufactured_step_from.'–'.$this->manufactured_step_to;
    }

    public function hasStepRange(): bool
    {
        return $this->step_from !== null && $this->step_to !== null;
    }

    public function stepRangeLabel(): string
    {
        if (! $this->hasStepRange()) {
            return '';
        }

        if ($this->step_from === $this->step_to) {
            return 'Step '.$this->step_from;
        }

        return 'Steps '.$this->step_from.'–'.$this->step_to;
    }

    public function stageLabel(): string
    {
        $prefix = $this->refinement_id ? 'Refinement · ' : '';

        if ($this->stage_number === null) {
            return $prefix.'Treatment case plan';
        }

        $label = $prefix.'Stage '.$this->stage_number;

        if ($this->isManufactured() && $this->manufacturedStepRangeLabel() !== '') {
            $label .= ' · '.$this->manufacturedStepRangeLabel().' manufactured';
        }

        return $label;
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function refinement(): BelongsTo
    {
        return $this->belongsTo(PatientCaseRefinement::class, 'refinement_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->review_status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->review_status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->review_status === self::STATUS_REJECTED;
    }

    public function reviewStatusLabel(): string
    {
        return match ($this->review_status) {
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Pending review',
        };
    }
}
