<?php

use App\Models\DoctorRole;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DoctorRole::query()->each(function (DoctorRole $role): void {
            $permissions = DoctorRole::normalizePermissions($role->permissions ?? []);

            if (in_array('request_refinement', $permissions, true)) {
                return;
            }

            $hasWorkflowAccess = count(array_intersect(
                $permissions,
                ['review_plans', 'request_modification']
            )) > 0;

            if (! $hasWorkflowAccess) {
                return;
            }

            $permissions[] = 'request_refinement';
            $role->update(['permissions' => array_values(array_unique($permissions))]);
        });
    }

    public function down(): void
    {
        // Permission grants cannot be safely reversed without data loss.
    }
};
