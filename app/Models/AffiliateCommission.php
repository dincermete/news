<?php

namespace App\Models;

use App\Enums\AffiliateCommissionStatus;
use Database\Factories\AffiliateCommissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

#[Fillable([
    'referrer_id',
    'referred_user_id',
    'order_id',
    'amount',
    'status',
])]
class AffiliateCommission extends Model
{
    /** @use HasFactory<AffiliateCommissionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => AffiliateCommissionStatus::class,
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new RuntimeException('AffiliateCommission kayıtları güncellenemez.');
        });

        static::deleting(function (): void {
            throw new RuntimeException('AffiliateCommission kayıtları silinemez.');
        });
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
