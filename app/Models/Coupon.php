<?php

namespace App\Models;

use App\Enums\CouponType;
use App\Exceptions\InvalidCouponException;
use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'type',
    'value',
    'valid_from',
    'valid_until',
    'usage_limit',
    'used_count',
    'min_cart_amount',
    'is_active',
])]
class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'used_count' => 0,
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'value' => 'decimal:2',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'min_cart_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function assertApplicable(float $subtotal): void
    {
        if (! $this->is_active) {
            throw InvalidCouponException::make('Kupon aktif değil.');
        }

        if ($this->valid_from && $this->valid_from->isFuture()) {
            throw InvalidCouponException::make('Kupon henüz geçerli değil.');
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            throw InvalidCouponException::make('Kupon süresi dolmuş.');
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            throw InvalidCouponException::make('Kupon kullanım limiti dolmuş.');
        }

        if ($this->min_cart_amount !== null && $subtotal < (float) $this->min_cart_amount) {
            throw InvalidCouponException::make('Sepet tutarı kupon için yetersiz.');
        }
    }

    public function discountAmount(float $subtotal): float
    {
        $amount = match ($this->type) {
            CouponType::Percentage => $subtotal * ((float) $this->value / 100),
            CouponType::FixedAmount => (float) $this->value,
        };

        return round(min($amount, $subtotal), 2);
    }
}
