<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'astrologer_id',
        'amount',
        'mode',
        'reference_no',
        'remarks',
        'proof',
        'transacted_at',
    ];

    protected $table = 'transactions';

    public function astrologer()
    {
        return $this->belongsTo(Astrologer::class, 'astrologer_id');
    }
}
