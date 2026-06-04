<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('clinic_name')->nullable()->after('website');
            $table->string('clinic_email')->nullable()->after('clinic_name');
            $table->string('clinic_phone', 50)->nullable()->after('clinic_email');
            $table->string('clinic_address', 500)->nullable()->after('clinic_phone');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['clinic_name', 'clinic_email', 'clinic_phone', 'clinic_address']);
        });
    }
};
