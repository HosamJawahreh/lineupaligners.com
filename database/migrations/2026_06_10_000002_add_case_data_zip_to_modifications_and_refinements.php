<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patient_case_modifications')
            && ! Schema::hasColumn('patient_case_modifications', 'case_data_zip')) {
            Schema::table('patient_case_modifications', function (Blueprint $table) {
                $table->string('case_data_zip')->nullable()->after('lower_jaw_scan_name');
                $table->string('case_data_zip_name')->nullable()->after('case_data_zip');
            });
        }

        if (Schema::hasTable('patient_case_refinements')
            && ! Schema::hasColumn('patient_case_refinements', 'case_data_zip')) {
            Schema::table('patient_case_refinements', function (Blueprint $table) {
                $table->string('case_data_zip')->nullable()->after('lower_jaw_scan_name');
                $table->string('case_data_zip_name')->nullable()->after('case_data_zip');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('patient_case_modifications')
            && Schema::hasColumn('patient_case_modifications', 'case_data_zip')) {
            Schema::table('patient_case_modifications', function (Blueprint $table) {
                $table->dropColumn(['case_data_zip', 'case_data_zip_name']);
            });
        }

        if (Schema::hasTable('patient_case_refinements')
            && Schema::hasColumn('patient_case_refinements', 'case_data_zip')) {
            Schema::table('patient_case_refinements', function (Blueprint $table) {
                $table->dropColumn(['case_data_zip', 'case_data_zip_name']);
            });
        }
    }
};
