<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Pack extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'cards_per_pack',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cards_per_pack' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsToMany<Card, $this> */
    public function cards(): BelongsToMany
    {
        return $this->belongsToMany(Card::class)->withPivot('weight')->withTimestamps();
    }

    /** @return HasMany<UserPack, $this> */
    public function userPacks(): HasMany
    {
        return $this->hasMany(UserPack::class);
    }

    /** @return HasMany<PackOpening, $this> */
    public function openings(): HasMany
    {
        return $this->hasMany(PackOpening::class);
    }

    /** @param Builder<Pack> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Somme des poids de la composition, denominateur des probabilites.
     */
    public function totalWeight(): int
    {
        return (int) $this->cards->sum(fn (Card $card) => $card->pivot->weight);
    }

    /**
     * Un pack n'est ouvrable que s'il contient de quoi tirer : au moins une
     * carte et une somme de poids strictement positive.
     */
    public function isOpenable(): bool
    {
        return $this->cards->isNotEmpty() && $this->totalWeight() > 0;
    }

    public function imageUrl(): string
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }

        return asset('images/pack-placeholder.svg');
    }
}
