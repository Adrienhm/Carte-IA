<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'card_type_id',
        'rarity_id',
        'value',
        'power',
        'defense',
        'image_path',
        'is_ai_generated',
        'image_prompt',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'power' => 'integer',
            'defense' => 'integer',
            'is_ai_generated' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<CardType, $this> */
    public function cardType(): BelongsTo
    {
        return $this->belongsTo(CardType::class);
    }

    /** @return BelongsTo<Rarity, $this> */
    public function rarity(): BelongsTo
    {
        return $this->belongsTo(Rarity::class);
    }

    /** @return BelongsToMany<Pack, $this> */
    public function packs(): BelongsToMany
    {
        return $this->belongsToMany(Pack::class)->withPivot('weight')->withTimestamps();
    }

    /** @return HasMany<UserCard, $this> */
    public function copies(): HasMany
    {
        return $this->hasMany(UserCard::class);
    }

    /** @param Builder<Card> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * URL de l'illustration, avec repli sur un visuel neutre tant que l'IA n'a
     * rien produit pour cette carte.
     */
    public function imageUrl(): string
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }

        return asset('images/card-placeholder.svg');
    }
}
