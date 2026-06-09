<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patient_manufacturing_stages')) {
            return;
        }

        Schema::create('patient_manufacturing_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('refinement_id')->nullable()->constrained('patient_case_refinements')->cascadeOnDelete();
            $table->unsignedSmallInteger('stage_number');
            $table->unsignedSmallInteger('manufactured_step_from');
            $table->unsignedSmallInteger('manufactured_step_to');
            $table->timestamp('manufactured_at');
            $table->foreignId('manufactured_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['patient_id', 'refinement_id', 'stage_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_manufacturing_stages');
    }
};
