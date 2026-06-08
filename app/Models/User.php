<?php

namespace App\Models;

use App\Models\Patient;
use App\Notifications\ResetPasswordNotification;
use App\Support\PublicStorageUrl;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'photo', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_DOCTOR = 'doctor';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isDoctor(): bool
    {
        return $this->role === self::ROLE_DOCTOR;
    }

    public function ownsPatient(Patient $patient): bool
    {
        if (! $this->isDoctor() || ! $this->doctor) {
            return false;
        }

        return $patient->doctor_id === $this->doctor->id;
    }

    public function doctorCan(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $role = $this->doctor?->doctorRole;

        return $this->isDoctor()
            && $role
            && $role->is_active
            && $role->hasPermission($permission);
    }

    public function displayName(): string
    {
        if ($this->isDoctor() && $this->doctor) {
            return $this->doctor->fullName();
        }

        return $this->name;
    }

    public function displayTitle(): string
    {
        if ($this->isDoctor() && $this->doctor?->specialty) {
            return $this->doctor->specialty;
        }

        return $this->isAdmin() ? 'Administrator' : 'User';
    }

    public function photoUrl(): string
    {
        $fallback = asset('assets/images/profile_av.jpg');

        if ($this->photo) {
            return PublicStorageUrl::url($this->photo, $fallback) ?? $fallback;
        }

        if ($this->isDoctor() && $this->doctor?->photo) {
            return $this->doctor->photoUrl();
        }

        return $fallback;
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
