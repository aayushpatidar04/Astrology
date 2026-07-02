<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    protected $fillable = [
        'astrologer_id',
        'account_holder',
        'account_number',
        'ifsc_code',
        'bank_name',
        'branch_name',
    ];

    protected $table = 'bank_details';

    public function astrologer()
    {
        return $this->belongsTo(Astrologer::class, 'astrologer_id');
    }
}
