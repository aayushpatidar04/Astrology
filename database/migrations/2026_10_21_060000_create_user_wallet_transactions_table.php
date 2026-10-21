<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('user_wallets')->cascadeOnDelete();
            $table->enum('type', ['earning', 'payout']);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->string('description');
            $table->timestamps();

            $table->unique(
                ['wallet_id', 'type', 'source_type', 'source_id'],
                'user_wallet_transactions_source_unique'
            );
        });

        $userIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'User')
            ->where('model_has_roles.model_type', \App\Models\User::class)
            ->distinct()
            ->pluck('model_has_roles.model_id')
            ->merge(
                DB::table('astrologers')->distinct()->pluck('user_id')
            )
            ->unique();

        foreach ($userIds as $userId) {
            $walletExists = DB::table('user_wallets')->where('user_id', $userId)->exists();

            if (! $walletExists) {
                DB::table('user_wallets')->insert([
                    'user_id' => $userId,
                    'balance' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_wallet_transactions');
    }
};
