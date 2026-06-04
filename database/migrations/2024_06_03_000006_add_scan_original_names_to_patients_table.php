<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('upper_jaw_scan_name')->nullable()->after('upper_jaw_scan');
            $table->string('lower_jaw_scan_name')->nullable()->after('lower_jaw_scan');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['upper_jaw_scan_name', 'lower_jaw_scan_name']);
        });
    }
};
