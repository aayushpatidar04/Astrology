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
        Schema::table('astrologers', function (Blueprint $table) {
            // Drop old column
            $table->dropColumn('pricing_per_minute');

            // Add new pricing columns
            $table->decimal('asked_call_price', 8, 2)->default(0);
            $table->decimal('charged_call_price', 8, 2)->default(0);
            $table->decimal('asked_text_price', 8, 2)->default(0);
            $table->decimal('charged_text_price', 8, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('astrologers', function (Blueprint $table) {
            // Rollback: remove new columns
            $table->dropColumn([
                'asked_call_price',
                'charged_call_price',
                'asked_text_price',
                'charged_text_price',
            ]);

            // Restore old column
            $table->decimal('pricing_per_minute', 8, 2)->default(0);
        });
    }
};
