<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DoctorRole extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }

    public function hasPermission(string $key): bool
    {
        return in_array($key, $this->normalizePermissions($this->permissions ?? []), true);
    }

    /** @param  array<int, string>  $permissions */
    public static function normalizePermissions(array $permissions): array
    {
        $current = array_keys(config('doctor-permissions.permissions', []));
        $legacyMap = config('doctor-permissions.legacy_map', []);
        $normalized = [];

        foreach ($permissions as $permission) {
            if (in_array($permission, $current, true)) {
                $normalized[] = $permission;

                continue;
            }

            if (! isset($legacyMap[$permission])) {
                continue;
            }

            foreach ((array) $legacyMap[$permission] as $mapped) {
                $normalized[] = $mapped;
            }
        }

        return array_values(array_unique(array_intersect($normalized, $current)));
    }

    public static function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $original.'-'.$count++;
        }

        return $slug;
    }
}
