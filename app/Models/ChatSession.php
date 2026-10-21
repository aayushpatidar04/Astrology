<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatSession extends Model
{
    protected $table = "chat_sessions";

    protected $fillable = [
        'chat_id',
        'session_reference',
        'user_id',
        'astrologer_id',
        'duration_seconds',
        'deduction',
        'ended_by',
        'ended_at',
    ];

    protected $casts = [
        'deduction' => 'decimal:2',
    ];

    public static function startFor(Chat $chat, int $userId, int $astrologerId): self
    {
        return DB::transaction(function () use ($chat, $userId, $astrologerId): self {
            Chat::whereKey($chat->getKey())->lockForUpdate()->firstOrFail();

            $activeSession = self::where('chat_id', $chat->getKey())
                ->where('user_id', $userId)
                ->where('astrologer_id', $astrologerId)
                ->whereNull('ended_at')
                ->latest('id')
                ->first();

            if ($activeSession) {
                return $activeSession;
            }

            return self::create([
                'chat_id' => $chat->getKey(),
                'session_reference' => (string) Str::uuid(),
                'user_id' => $userId,
                'astrologer_id' => $astrologerId,
            ]);
        });
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function astrologer()
    {
        return $this->belongsTo(User::class, 'astrologer_id');
    }
}
