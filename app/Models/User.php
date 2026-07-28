<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'banned_at',
        'ban_reason',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'banned_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }

    /** @return HasMany<UserCard, $this> */
    public function cards(): HasMany
    {
        return $this->hasMany(UserCard::class);
    }

    /** @return HasMany<UserPack, $this> */
    public function packs(): HasMany
    {
        return $this->hasMany(UserPack::class);
    }

    /** @return HasMany<PackOpening, $this> */
    public function packOpenings(): HasMany
    {
        return $this->hasMany(PackOpening::class);
    }

    /** @return HasMany<Trade, $this> */
    public function sentTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'sender_id');
    }

    /** @return HasMany<Trade, $this> */
    public function receivedTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'receiver_id');
    }

    public function collectionValue(): int
    {
        return (int) $this->cards()
            ->join('cards', 'cards.id', '=', 'user_cards.card_id')
            ->sum('cards.value');
    }
}
