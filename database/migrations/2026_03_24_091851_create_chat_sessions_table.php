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
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // paying user
            $table->foreignId('astrologer_id')->constrained('users')->onDelete('cascade'); // astrologer
            $table->integer('duration_seconds')->default(0); // total chat duration
            $table->decimal('deduction', 10, 2)->default(0); // wallet deduction
            $table->string('ended_by')->nullable(); // 'user', 'astrologer', 'balance'
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};
