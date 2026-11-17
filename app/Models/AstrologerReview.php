<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AstrologerReview extends Model
{
    protected $table = 'astrologer_reviews';

    protected $fillable = [
        'astrologer_id',
        'user_id',
        'rating',
        'review',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function astrologer(): BelongsTo
    {
        return $this->belongsTo(Astrologer::class, 'astrologer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
