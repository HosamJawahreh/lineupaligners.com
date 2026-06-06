<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_photos', function (Blueprint $table) {
            $table->foreignId('modification_id')
                ->nullable()
                ->after('patient_id')
                ->constrained('patient_case_modifications')
                ->cascadeOnDelete();
            $table->foreignId('refinement_id')
                ->nullable()
                ->after('modification_id')
                ->constrained('patient_case_refinements')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('patient_photos')) {
            return;
        }

        Schema::table('patient_photos', function (Blueprint $table) {
            if (Schema::hasColumn('patient_photos', 'refinement_id')) {
                $table->dropConstrainedForeignId('refinement_id');
            }
            if (Schema::hasColumn('patient_photos', 'modification_id')) {
                $table->dropConstrainedForeignId('modification_id');
            }
        });
    }
};
