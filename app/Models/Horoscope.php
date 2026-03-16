<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horoscope extends Model
{
    protected $table = "horoscopes";

    protected $fillable = [
        'sign',
        'colors',
        'numbers',
        'alphabets',
        'love',
        'health',
        'career',
        'emotions',
        'travel',
        'description',
        'cosmic_tip',
        'tip_for_singles',
        'tip_for_couples',
        'type',
        'date',
        'week_key',
        'month_key',
        'year_key',
    ];

    public function scopeDaily($query)
    {
        return $query->where('type', 'daily');
    }

    public function scopeWeekly($query)
    {
        return $query->where('type', 'weekly');
    }

    public function scopeMonthly($query)
    {
        return $query->where('type', 'monthly');
    }

    public function scopeYearly($query)
    {
        return $query->where('type', 'yearly');
    }
}
