<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateUsers = DB::table('user_wallets')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id');

        foreach ($duplicateUsers as $userId) {
            $walletIds = DB::table('user_wallets')
                ->where('user_id', $userId)
                ->orderBy('id')
                ->pluck('id');
            $primaryWalletId = $walletIds->shift();
            $totalBalance = DB::table('user_wallets')
                ->where('user_id', $userId)
                ->sum('balance');

            DB::table('user_wallets')
                ->where('id', $primaryWalletId)
                ->update(['balance' => $totalBalance]);

            DB::table('user_wallets')->whereIn('id', $walletIds)->delete();
        }

        Schema::table('user_wallets', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_wallets', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }
};
