<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarot_readings_cache', function (Blueprint $table) {
            $table->id();
            $table->string('cache_key', 64)->unique();
            $table->string('spread_type');
            $table->string('category');
            $table->text('question')->nullable();
            $table->string('overall_theme');
            $table->json('cards'); // Stores drawn cards, IDs, and orientations
            $table->text('synthesis');
            $table->text('actionable_advice');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarot_readings_cache');
    }
};
