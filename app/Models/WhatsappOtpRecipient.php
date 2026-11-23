<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappOtpRecipient extends Model
{
    protected $table = 'whatsapp_otp_recipients';

    protected $fillable = [
        'phone',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }
}
