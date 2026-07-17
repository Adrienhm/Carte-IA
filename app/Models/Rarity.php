<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rarity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'default_weight',
        'base_value',
        'min_stat',
        'max_stat',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'default_weight' => 'integer',
            'base_value' => 'integer',
            'min_stat' => 'integer',
            'max_stat' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /** @return HasMany<Card, $this> */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
}
