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
        Schema::create('astrologers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->references('id')->onDelete('cascade');
            $table->text('bio')->nullable();
            $table->json('expertise');
            $table->integer('experience_years');
            $table->json('documents')->nullable();
            $table->string('profile_image');
            $table->decimal('pricing_per_minute', 10, 2)->default(0);
            $table->boolean('online')->default(0);
            $table->enum('status', ['pending_verification', 'verified', 'active', 'rejected']);
            $table->foreignId('verified_by')->nullable()->constrained('users')->references('id');
            $table->timestamp('verified_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('astrologers');
    }
};
