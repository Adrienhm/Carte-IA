<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_id',
        'source',
        'pack_opening_id',
        'locked_by_trade_id',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Card, $this> */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    /** @return BelongsTo<Trade, $this> */
    public function lockingTrade(): BelongsTo
    {
        return $this->belongsTo(Trade::class, 'locked_by_trade_id');
    }

    public function isLocked(): bool
    {
        return $this->locked_by_trade_id !== null;
    }

    /**
     * @param Builder<UserCard> $query
     */
    public function scopeAvailable(Builder $query): void
    {
        $query->whereNull('locked_by_trade_id');
    }
}
