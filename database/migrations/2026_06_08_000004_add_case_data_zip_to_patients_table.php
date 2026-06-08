<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('case_data_zip')->nullable()->after('lower_jaw_scan_name');
            $table->string('case_data_zip_name')->nullable()->after('case_data_zip');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['case_data_zip', 'case_data_zip_name']);
        });
    }
};
