<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneOtp extends Model
{
    protected $table = 'phone_otps';

    protected $fillable = [
        'phone',
        'otp',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
