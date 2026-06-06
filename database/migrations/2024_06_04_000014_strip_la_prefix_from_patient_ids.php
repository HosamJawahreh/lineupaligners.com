<?php

use App\Models\Patient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('patients')
            ->where('patient_id', 'like', 'LA %')
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $row): void {
                $normalized = Patient::normalizePatientId($row->patient_id);

                if ($normalized === $row->patient_id) {
                    return;
                }

                DB::table('patients')
                    ->where('id', $row->id)
                    ->update(['patient_id' => $normalized]);
            });
    }

    public function down(): void
    {
        DB::table('patients')
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $row): void {
                if (preg_match('/^\d+$/', (string) $row->patient_id) !== 1) {
                    return;
                }

                DB::table('patients')
                    ->where('id', $row->id)
                    ->update(['patient_id' => 'LA '.$row->patient_id]);
            });
    }
};
