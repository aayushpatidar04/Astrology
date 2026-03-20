<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use SoftDeletes;

    protected $table = "chats";

    protected $fillable = [
        'name'
    ];

    public function participants()
    {
        return $this->hasMany(ChatParticipant::class, 'chat_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'chat_id')->latestOfMany();
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'chat_id');
    }
}
