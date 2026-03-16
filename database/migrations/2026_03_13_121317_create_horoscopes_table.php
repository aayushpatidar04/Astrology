<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('horoscopes', function (Blueprint $table) {
            $table->id();
            $table->string('sign'); // Aries, Taurus, etc.
            $table->text('colors')->nullable();
            $table->text('numbers')->nullable();
            $table->text('alphabets')->nullable();
            $table->text('love')->nullable();
            $table->text('health')->nullable();
            $table->text('career')->nullable();
            $table->text('emotions')->nullable();
            $table->text('travel')->nullable();
            $table->text('description')->nullable();
            $table->text('cosmic_tip')->nullable();
            $table->text('tip_for_singles')->nullable();
            $table->text('tip_for_couples')->nullable();
            $table->enum('type', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->string('date')->nullable();
            $table->string('week_key', 7)->nullable();
            $table->string('month_key', 7)->nullable();
            $table->string('year_key', 4)->nullable();
            $table->timestamps();

            $table->index(['sign', 'type']);
            $table->index('date');
            $table->index('week_key');
            $table->index('month_key');
            $table->index('year_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horoscopes');
    }
};
