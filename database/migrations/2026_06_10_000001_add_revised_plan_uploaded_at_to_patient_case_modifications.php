<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('patient_case_modifications')) {
            return;
        }

        if (Schema::hasColumn('patient_case_modifications', 'revised_plan_uploaded_at')) {
            return;
        }

        Schema::table('patient_case_modifications', function (Blueprint $table) {
            $table->timestamp('revised_plan_uploaded_at')->nullable()->after('revised_plan_url');
        });

        DB::table('patient_case_modifications')
            ->whereNotNull('revised_plan_url')
            ->whereNull('revised_plan_uploaded_at')
            ->update(['revised_plan_uploaded_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('patient_case_modifications')) {
            return;
        }

        if (! Schema::hasColumn('patient_case_modifications', 'revised_plan_uploaded_at')) {
            return;
        }

        Schema::table('patient_case_modifications', function (Blueprint $table) {
            $table->dropColumn('revised_plan_uploaded_at');
        });
    }
};
