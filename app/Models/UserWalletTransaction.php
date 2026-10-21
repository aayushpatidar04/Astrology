<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'balance_after',
        'source_type',
        'source_id',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function wallet()
    {
        return $this->belongsTo(UserWallet::class, 'wallet_id');
    }
}
