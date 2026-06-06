<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_showcases', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('patient_label')->nullable();
            $table->string('case_type')->default('full_case');
            $table->unsignedSmallInteger('treatment_months')->nullable();
            $table->text('summary')->nullable();
            $table->text('outcome')->nullable();
            $table->string('before_image')->nullable();
            $table->string('after_image')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_showcases');
    }
};
