<?php

namespace App\Models;

use App\Enums\SpinCreditTransactionType;
use Database\Factories\SpinCreditTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'type',
    'amount',
    'reason',
    'related_payment_id',
])]
class SpinCreditTransaction extends Model
{
    /** @use HasFactory<SpinCreditTransactionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => SpinCreditTransactionType::class,
            'amount' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): bool {
            return false;
        });

        static::deleting(function (): bool {
            return false;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function relatedPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'related_payment_id');
    }
}
