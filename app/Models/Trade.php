<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trade extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'status',
        'message',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /** @return BelongsTo<User, $this> */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /** @return HasMany<TradeItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(TradeItem::class);
    }

    /** @return HasMany<TradeItem, $this> */
    public function offeredItems(): HasMany
    {
        return $this->items()->where('side', TradeItem::SIDE_OFFERED);
    }

    /** @return HasMany<TradeItem, $this> */
    public function requestedItems(): HasMany
    {
        return $this->items()->where('side', TradeItem::SIDE_REQUESTED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /** @param Builder<Trade> $query */
    public function scopePending(Builder $query): void
    {
        $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Un echange concerne exactement deux joueurs : toute action dessus doit
     * etre verifiee cote serveur contre cette liste (CDC 5.1 securite).
     */
    public function involves(User $user): bool
    {
        return $this->sender_id === $user->id || $this->receiver_id === $user->id;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_ACCEPTED => 'Accepte',
            self::STATUS_REJECTED => 'Refuse',
            self::STATUS_CANCELLED => 'Annule',
            default => $this->status,
        };
    }
}
