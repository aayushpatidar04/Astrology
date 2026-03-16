<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Astrologer extends Model
{
    use SoftDeletes;

    protected $table = "astrologers";

    protected $fillable = [
        'user_id',
        'bio',
        'expertise',
        'experience_years',
        'documents',
        'profile_image',
        'asked_call_price',
        'charged_call_price',
        'asked_text_price',
        'charged_text_price',
        'online',
        'status',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'expertise' => 'array',
        'documents' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
