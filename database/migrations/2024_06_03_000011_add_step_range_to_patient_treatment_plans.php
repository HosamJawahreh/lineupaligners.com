<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_treatment_plans', function (Blueprint $table) {
            $table->unsignedSmallInteger('step_from')->nullable()->after('stage_number');
            $table->unsignedSmallInteger('step_to')->nullable()->after('step_from');
        });
    }

    public function down(): void
    {
        Schema::table('patient_treatment_plans', function (Blueprint $table) {
            $table->dropColumn(['step_from', 'step_to']);
        });
    }
};
