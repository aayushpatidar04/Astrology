<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDetail extends Model
{
    use SoftDeletes;

    protected $table = "user_details";

    protected $fillable = [
        'user_id',
        'dob',
        'birth_time',
        'gender',
        'location',
        'profile_image',
        'preferred_language',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

}
