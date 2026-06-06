<?php

use App\Models\DoctorRole;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DoctorRole::query()->each(function (DoctorRole $role): void {
            $permissions = DoctorRole::normalizePermissions($role->permissions ?? []);

            if ($permissions !== ($role->permissions ?? [])) {
                $role->update(['permissions' => $permissions]);
            }
        });
    }

    public function down(): void
    {
        // Permission keys cannot be safely reversed without data loss.
    }
};
