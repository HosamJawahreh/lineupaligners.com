<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_showcases', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
            $table->json('detail')->nullable()->after('after_image');
        });
    }

    public function down(): void
    {
        Schema::table('website_showcases', function (Blueprint $table) {
            $table->dropColumn(['slug', 'detail']);
        });
    }
};
