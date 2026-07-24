<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\StoryFormat;
use Database\Factories\InstagramStoryPriceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'instagram_account_id',
    'format',
    'price',
    'currency',
    'is_active',
])]
class InstagramStoryPrice extends Model
{
    /** @use HasFactory<InstagramStoryPriceFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => 'TRY',
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'format' => StoryFormat::class,
            'price' => 'decimal:2',
            'currency' => Currency::class,
            'is_active' => 'boolean',
        ];
    }

    public function instagramAccount(): BelongsTo
    {
        return $this->belongsTo(InstagramAccount::class);
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
