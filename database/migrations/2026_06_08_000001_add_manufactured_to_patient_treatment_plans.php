<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_treatment_plans', function (Blueprint $table) {
            $table->timestamp('manufactured_at')->nullable()->after('is_current');
            $table->foreignId('manufactured_by')->nullable()->after('manufactured_at')->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('manufactured_step_from')->nullable()->after('manufactured_by');
            $table->unsignedSmallInteger('manufactured_step_to')->nullable()->after('manufactured_step_from');
        });
    }

    public function down(): void
    {
        Schema::table('patient_treatment_plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manufactured_by');
            $table->dropColumn(['manufactured_at', 'manufactured_step_from', 'manufactured_step_to']);
        });
    }
};
