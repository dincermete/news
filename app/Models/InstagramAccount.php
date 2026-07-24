<?php

namespace App\Models;

use App\Enums\SiteStatus;
use Database\Factories\InstagramAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'handle',
    'name',
    'avatar_url',
    'follower_count',
    'status',
])]
class InstagramAccount extends Model
{
    /** @use HasFactory<InstagramAccountFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'follower_count' => 'integer',
            'status' => SiteStatus::class,
        ];
    }

    public function storyPrices(): HasMany
    {
        return $this->hasMany(InstagramStoryPrice::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
