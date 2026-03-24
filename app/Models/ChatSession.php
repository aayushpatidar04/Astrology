<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    protected $table = "chat_sessions";

    protected $fillable = [
        'chat_id',
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
