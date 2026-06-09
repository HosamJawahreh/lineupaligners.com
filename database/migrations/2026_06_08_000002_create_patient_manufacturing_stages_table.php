<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const UNIQUE_INDEX = 'pmfg_stages_unique';

    public function up(): void
    {
        if (! Schema::hasTable('patient_manufacturing_stages')) {
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

                $table->unique(['patient_id', 'refinement_id', 'stage_number'], self::UNIQUE_INDEX);
            });

            return;
        }

        if (! $this->indexExists(self::UNIQUE_INDEX)) {
            Schema::table('patient_manufacturing_stages', function (Blueprint $table) {
                $table->unique(['patient_id', 'refinement_id', 'stage_number'], self::UNIQUE_INDEX);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_manufacturing_stages');
    }

    private function indexExists(string $indexName): bool
    {
        $indexes = DB::select(
            'SHOW INDEX FROM patient_manufacturing_stages WHERE Key_name = ?',
            [$indexName]
        );

        return count($indexes) > 0;
    }
};
