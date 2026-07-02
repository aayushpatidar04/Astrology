<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('astrologer_id')->constrained('astrologers')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('mode'); // NEFT, Bank Transfer
            $table->string('reference_no')->nullable();
            $table->text('remarks')->nullable();
            $table->string('proof')->nullable(); // Path to the proof of transaction
            $table->timestamp('transacted_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
