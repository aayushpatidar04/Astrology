<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarotReadingCache extends Model
{
    protected $table = 'tarot_readings_cache';

    protected $casts = [
        'cards' => 'array',
    ];

    protected $fillable = [
        'cache_key',
        'spread_type',
        'category',
        'question',
        'overall_theme',
        'cards',
        'synthesis',
        'actionable_advice',
    ];
}
