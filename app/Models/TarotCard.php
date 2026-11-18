<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarotCard extends Model
{
    protected $table = 'tarot_cards';

    protected $fillable = [
        'card_number',
        'name',
        'slug',
        'arcana',
        'suit',
        'element',
        'keywords_upright',
        'keywords_reversed',
        'upright_general',
        'reversed_general',
        'upright_love',
        'reversed_love',
        'upright_career',
        'reversed_career',
        'upright_finance',
        'reversed_finance',
    ];

    /**
     * Get the consistent, fixed interpretation for a given orientation and category
     */
    public function getMeaningFor(bool $isReversed, string $category): string
    {
        $prefix = $isReversed ? 'reversed_' : 'upright_';
        $cat = strtolower(trim($category));

        if (str_contains($cat, 'love') || str_contains($cat, 'marriage') || str_contains($cat, 'relationship')) {
            return $this->{$prefix . 'love'};
        }

        if (str_contains($cat, 'career') || str_contains($cat, 'business') || str_contains($cat, 'job')) {
            return $this->{$prefix . 'career'};
        }

        if (str_contains($cat, 'finance') || str_contains($cat, 'wealth') || str_contains($cat, 'money')) {
            return $this->{$prefix . 'finance'};
        }

        return $this->{$prefix . 'general'};
    }
}
