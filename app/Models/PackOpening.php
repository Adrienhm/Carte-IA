<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trace d'une ouverture de pack : quel joueur, quel pack, quelles cartes.
 */
class PackOpening extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pack_id',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Pack, $this> */
    public function pack(): BelongsTo
    {
        return $this->belongsTo(Pack::class);
    }

    /** @return HasMany<UserCard, $this> */
    public function userCards(): HasMany
    {
        return $this->hasMany(UserCard::class);
    }
}
