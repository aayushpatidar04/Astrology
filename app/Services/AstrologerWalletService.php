<?php

namespace App\Services;

use App\Models\Astrologer;
use App\Models\Transaction;
use App\Models\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AstrologerWalletService
{
    public function recordEarning(
        Astrologer $astrologer,
        string $sourceType,
        int $sourceId,
        string $description,
        float $amount
    ): void {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($astrologer, $sourceType, $sourceId, $description, $amount): void {
            $wallet = UserWallet::firstOrCreate(
                ['user_id' => $astrologer->user_id],
                ['balance' => 0]
            );
            $wallet = UserWallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            $alreadyRecorded = $wallet->transactions()
                ->where('type', 'earning')
                ->where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->exists();

            if ($alreadyRecorded) {
                return;
            }

            $wallet->balance = (float) $wallet->balance + $amount;
            $wallet->save();

            $wallet->transactions()->create([
                'type' => 'earning',
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'description' => $description,
            ]);
        });
    }

    public function recordPayout(Astrologer $astrologer, Transaction $payout): void
    {
        DB::transaction(function () use ($astrologer, $payout): void {
            $wallet = UserWallet::firstOrCreate(
                ['user_id' => $astrologer->user_id],
                ['balance' => 0]
            );
            $wallet = UserWallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            $alreadyRecorded = $wallet->transactions()
                ->where('type', 'payout')
                ->where('source_type', 'payout')
                ->where('source_id', $payout->id)
                ->exists();

            if ($alreadyRecorded) {
                return;
            }

            $payoutAmount = (float) $payout->amount;
            if ($payoutAmount <= 0 || $payoutAmount > (float) $wallet->balance) {
                throw ValidationException::withMessages([
                    'amount' => 'The payout amount cannot exceed the astrologer wallet balance.',
                ]);
            }

            $wallet->balance = (float) $wallet->balance - $payoutAmount;
            $wallet->save();

            $wallet->transactions()->create([
                'type' => 'payout',
                'amount' => $payoutAmount,
                'balance_after' => $wallet->balance,
                'source_type' => 'payout',
                'source_id' => $payout->id,
                'description' => 'Payout recorded: '.$payout->mode,
            ]);
        });
    }
}
