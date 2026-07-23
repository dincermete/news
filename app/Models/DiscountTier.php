<?php

namespace App\Models;

use Database\Factories\DiscountTierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'min_cart_amount',
    'discount_percentage',
    'is_active',
    'sort_order',
])]
class DiscountTier extends Model
{
    /** @use HasFactory<DiscountTierFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_cart_amount' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<DiscountTier>  $query
     * @return Builder<DiscountTier>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function bestForAmount(float $subtotal): ?self
    {
        return static::query()
            ->active()
            ->where('min_cart_amount', '<=', $subtotal)
            ->orderByDesc('min_cart_amount')
            ->orderByDesc('discount_percentage')
            ->first();
    }

    public function discountAmount(float $subtotal): float
    {
        return round($subtotal * ((float) $this->discount_percentage / 100), 2);
    }
}
