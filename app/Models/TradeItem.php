<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeItem extends Model
{
    use HasFactory;

    public const SIDE_OFFERED = 'offered';
    public const SIDE_REQUESTED = 'requested';

    protected $fillable = [
        'trade_id',
        'user_card_id',
        'side',
    ];

    /** @return BelongsTo<Trade, $this> */
    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    /** @return BelongsTo<UserCard, $this> */
    public function userCard(): BelongsTo
    {
        return $this->belongsTo(UserCard::class);
    }
}
