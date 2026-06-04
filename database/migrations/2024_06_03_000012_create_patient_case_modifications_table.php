<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patient_case_modifications')) {
            return;
        }

        Schema::create('patient_case_modifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('stage_number')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->boolean('is_current')->default(true);
            $table->string('upper_jaw_scan')->nullable();
            $table->string('upper_jaw_scan_name')->nullable();
            $table->string('lower_jaw_scan')->nullable();
            $table->string('lower_jaw_scan_name')->nullable();
            $table->text('notes');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('treatment_plan_id')->nullable()->constrained('patient_treatment_plans')->nullOnDelete();
            $table->timestamps();

            $table->index(['patient_id', 'stage_number', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_case_modifications');
    }
};
