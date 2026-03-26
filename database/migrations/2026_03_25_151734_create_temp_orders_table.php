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
        Schema::create('temp_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('phone');
            $table->foreignId('reacharge_package_id')->constrained('recharge_packages')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('bonus_amount', 10, 2)->default(0);
            $table->decimal('payable_amount', 10, 2);
            $table->enum('status', ['pending','failed','expired'])->default('pending');
            $table->string('payment_gateway')->nullable();
            $table->string('transaction_ref')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_orders');
    }
};
