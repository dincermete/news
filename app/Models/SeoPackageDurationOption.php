<?php

namespace App\Models;

use Database\Factories\SeoPackageDurationOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'months',
    'price_multiplier',
    'bonus_label',
    'is_active',
    'sort_order',
])]
class SeoPackageDurationOption extends Model
{
    /** @use HasFactory<SeoPackageDurationOptionFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'price_multiplier' => 1,
        'is_active' => true,
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'months' => 'integer',
            'price_multiplier' => 'decimal:4',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function resolvePrice(float|string $monthlyPrice): float
    {
        return round((float) $monthlyPrice * (float) $this->months * (float) $this->price_multiplier, 2);
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
