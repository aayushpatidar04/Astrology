<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use SoftDeletes;

    protected $table = "chat_messages";

    protected $fillable = [
        'chat_id',
        'user_id',
        'type',
        'message',
    ];

    public function chat(){
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
