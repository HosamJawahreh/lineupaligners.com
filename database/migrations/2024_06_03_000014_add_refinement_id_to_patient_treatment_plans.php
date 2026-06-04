<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('patient_treatment_plans')) {
            return;
        }

        if (Schema::hasColumn('patient_treatment_plans', 'refinement_id')) {
            return;
        }

        Schema::table('patient_treatment_plans', function (Blueprint $table) {
            $table->foreignId('refinement_id')
                ->nullable()
                ->after('patient_id')
                ->constrained('patient_case_refinements')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('patient_treatment_plans', 'refinement_id')) {
            return;
        }

        Schema::table('patient_treatment_plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('refinement_id');
        });
    }
};
