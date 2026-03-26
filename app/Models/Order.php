<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = "orders";

    protected $fillable = [
        'user_id',
        'phone',
        'recharge_package_id',
        'amount',
        'bonus_amount',
        'payable_amount',
        'status',
        'payment_gateway',
        'transaction_ref',
    ];

    protected $cast = [
        'amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'payable_amount' => 'decimal:2',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function package(){
        return $this->belongsTo(RechargePackage::class, 'recharge_package_id');
    }
}
