<?php

namespace App\Services;

use App\Models\Astrologer;
use App\Models\Chat;
use App\Models\ChatSession;
use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Support\Facades\DB;

class ChatSessionSettlementService
{
    public function settle(
        Chat $chat,
        User $customer,
        ?Astrologer $astrologer,
        int $astrologerUserId,
        int $elapsedSeconds,
        ?string $sessionReference,
        ?string $reason
    ): float {
        return DB::transaction(function () use (
            $chat,
            $customer,
            $astrologer,
            $astrologerUserId,
            $elapsedSeconds,
            $sessionReference,
            $reason
        ): float {
            Chat::whereKey($chat->id)->lockForUpdate()->firstOrFail();

            $sessionQuery = ChatSession::query();
            if ($sessionReference) {
                $sessionQuery->where('session_reference', $sessionReference);
            } else {
                $sessionQuery->where('chat_id', $chat->id)
                    ->where('user_id', $customer->id)
                    ->where('astrologer_id', $astrologerUserId)
                    ->whereNull('ended_at')
                    ->latest('id');
            }
            $session = $sessionQuery->lockForUpdate()->first();

            if (! $session) {
                abort(409, 'No active chat session was found.');
            }

            abort_unless(
                $session->chat_id === $chat->id
                    && $session->user_id === $customer->id
                    && $session->astrologer_id === $astrologerUserId,
                403
            );

            if ($session->ended_at) {
                return (float) $session->deduction;
            }

            $customer->ensureWallet();
            $wallet = UserWallet::where('user_id', $customer->id)->lockForUpdate()->firstOrFail();
            $chargedRate = (float) ($astrologer?->charged_text_price ?? 0);
            $requestedDeduction = round(($elapsedSeconds / 60) * $chargedRate, 2);
            $deduction = min($requestedDeduction, (float) $wallet->balance);

            if ($deduction > 0) {
                $wallet->decrement('balance', $deduction);
            }

            $session->duration_seconds = $elapsedSeconds;
            $session->deduction = $deduction;
            $session->ended_by = $reason;
            $session->ended_at = now();
            $session->save();

            if ($astrologer) {
                $earning = min(
                    $deduction,
                    round(($elapsedSeconds / 60) * (float) $astrologer->asked_text_price, 2)
                );
                app(AstrologerWalletService::class)->recordEarning(
                    $astrologer,
                    'chat_session',
                    $session->id,
                    'Chat session earnings',
                    $earning
                );
            }

            return $deduction;
        });
    }
}
