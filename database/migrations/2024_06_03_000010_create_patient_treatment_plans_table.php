<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_treatment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('stage_number')->nullable();
            $table->string('plan_url', 2048);
            $table->string('review_status', 20)->default('pending');
            $table->text('review_comment')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('version')->default(1);
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->index(['patient_id', 'stage_number', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_treatment_plans');
    }
};
