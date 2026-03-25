<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RechargePackage extends Model
{
    protected $table = "recharge_packages";

    protected $fillable = [
        'amount',
        'bonus_amount',
        'label',
        'recommended',
        'type',
    ];
}
