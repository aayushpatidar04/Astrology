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
        Schema::create('recharge_packages', function (Blueprint $table) {
            $table->id();
            $table->integer('amount'); // base recharge amount
            $table->integer('bonus_amount')->nullable(); // extra credits or wallet bonus
            $table->string('label')->nullable(); // e.g. "GET 30% EXTRA"
            $table->boolean('recommended')->default(false);
            $table->enum('type', ['first_time', 'regular', 'special'])->default('regular');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recharge_packages');
    }
};
