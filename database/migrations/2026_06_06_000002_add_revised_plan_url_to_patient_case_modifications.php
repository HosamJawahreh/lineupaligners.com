<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('patient_case_modifications')) {
            return;
        }

        if (Schema::hasColumn('patient_case_modifications', 'revised_plan_url')) {
            return;
        }

        Schema::table('patient_case_modifications', function (Blueprint $table) {
            $table->string('revised_plan_url', 2048)->nullable()->after('treatment_plan_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('patient_case_modifications')) {
            return;
        }

        if (! Schema::hasColumn('patient_case_modifications', 'revised_plan_url')) {
            return;
        }

        Schema::table('patient_case_modifications', function (Blueprint $table) {
            $table->dropColumn('revised_plan_url');
        });
    }
};
