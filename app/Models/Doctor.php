<?php

namespace App\Models;

use App\Models\Setting;
use App\Support\PublicStorageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $fillable = [
        'user_id',
        'doctor_role_id',
        'department_id',
        'first_name',
        'last_name',
        'specialty',
        'phone',
        'email',
        'date_of_birth',
        'gender',
        'photo',
        'bio',
        'website',
        'experience_years',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctorRole(): BelongsTo
    {
        return $this->belongsTo(DoctorRole::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function clinicNameForDisplay(): string
    {
        if (filled($this->clinic_name)) {
            return $this->clinic_name;
        }

        return Setting::get('clinic_name', config('app.name'));
    }

    public function clinicEmailForDisplay(): ?string
    {
        return filled($this->clinic_email) ? $this->clinic_email : Setting::get('clinic_email');
    }

    public function clinicPhoneForDisplay(): ?string
    {
        return filled($this->clinic_phone) ? $this->clinic_phone : Setting::get('clinic_phone');
    }

    public function clinicAddressForDisplay(): ?string
    {
        return filled($this->clinic_address) ? $this->clinic_address : Setting::get('clinic_address');
    }

    public function photoUrl(): string
    {
        $fallback = asset('assets/images/xs/avatar2.jpg');

        if ($this->photo) {
            return PublicStorageUrl::url($this->photo, $fallback) ?? $fallback;
        }

        return $fallback;
    }
}
