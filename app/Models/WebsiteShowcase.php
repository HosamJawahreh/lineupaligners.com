<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WebsiteShowcase extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'patient_label',
        'case_type',
        'treatment_months',
        'summary',
        'outcome',
        'before_image',
        'after_image',
        'detail',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'treatment_months' => 'integer',
            'sort_order' => 'integer',
            'detail' => 'array',
        ];
    }

    public function caseTypeLabel(): string
    {
        return config('website.case_types.'.$this->case_type, ucfirst(str_replace('_', ' ', $this->case_type)));
    }

    public function beforeImageUrl(): ?string
    {
        return $this->imageUrl($this->before_image);
    }

    public function afterImageUrl(): ?string
    {
        return $this->imageUrl($this->after_image);
    }

    public function hasImages(): bool
    {
        return $this->before_image || $this->after_image;
    }

    private function imageUrl(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return asset('storage/'.$path);
    }

    public static function nextSortOrder(): int
    {
        return (int) (static::max('sort_order') ?? 0) + 1;
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
