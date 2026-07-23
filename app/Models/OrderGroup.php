<?php

namespace App\Models;

use App\Enums\Currency;
use Database\Factories\OrderGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'subtotal',
    'discount_tier_amount',
    'coupon_discount_amount',
    'vat_amount',
    'vat_withholding_amount',
    'total',
    'currency',
    'billing_profile_id',
    'contract_accepted_at',
])]
class OrderGroup extends Model
{
    /** @use HasFactory<OrderGroupFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'subtotal' => 0,
        'discount_tier_amount' => 0,
        'coupon_discount_amount' => 0,
        'total' => 0,
        'currency' => 'TRY',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_tier_amount' => 'decimal:2',
            'coupon_discount_amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'vat_withholding_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'currency' => Currency::class,
            'contract_accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function billingProfile(): BelongsTo
    {
        return $this->belongsTo(BillingProfile::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function couponRedemption(): HasOne
    {
        return $this->hasOne(CouponRedemption::class);
    }
}
