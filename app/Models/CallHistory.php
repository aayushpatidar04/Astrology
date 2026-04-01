<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallHistory extends Model
{
    protected $table = "call_histories";

    protected $fillable = [
        'user_id',
        'astrologer_id',
        'call_type',
        'status',
        'started_at',
        'ended_at',
        'duration',
        'cost',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function astrologer()
    {
        return $this->belongsTo(User::class, 'astrologer_id');
    }
}
