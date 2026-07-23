<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionType;
use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'user_id',
    'balance',
    'currency',
])]
class Wallet extends Model
{
    /** @use HasFactory<WalletFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'balance' => 0,
        'currency' => 'TRY',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'currency' => Currency::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public static function forUser(User $user, Currency $currency = Currency::Try): self
    {
        return static::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'currency' => $currency,
            ],
            [
                'balance' => 0,
            ],
        );
    }

    public function bucketBalance(WalletBalanceType $type): float
    {
        $credits = (float) $this->transactions()
            ->where('balance_type', $type)
            ->where('type', WalletTransactionType::Credit)
            ->sum('amount');

        $debits = (float) $this->transactions()
            ->where('balance_type', $type)
            ->where('type', WalletTransactionType::Debit)
            ->sum('amount');

        return round($credits - $debits, 2);
    }

    public function totalAvailableBalance(): float
    {
        $total = 0.0;

        foreach (WalletBalanceType::cases() as $type) {
            $total += $this->bucketBalance($type);
        }

        return round($total, 2);
    }

    public function credit(
        float|string $amount,
        string $reason,
        ?Order $order = null,
        WalletBalanceType $balanceType = WalletBalanceType::Main,
        ?Payment $payment = null,
    ): WalletTransaction {
        return DB::transaction(function () use ($amount, $reason, $order, $balanceType, $payment): WalletTransaction {
            $wallet = static::query()->lockForUpdate()->findOrFail($this->id);

            $wallet->balance = round((float) $wallet->balance + (float) $amount, 2);
            $wallet->save();

            return $wallet->transactions()->create([
                'type' => WalletTransactionType::Credit,
                'amount' => $amount,
                'reason' => $reason,
                'balance_type' => $balanceType,
                'related_order_id' => $order?->id,
                'related_payment_id' => $payment?->id,
            ]);
        });
    }

    public function debit(
        float|string $amount,
        string $reason,
        ?Order $order = null,
        WalletBalanceType $balanceType = WalletBalanceType::Main,
        ?Payment $payment = null,
    ): WalletTransaction {
        return DB::transaction(function () use ($amount, $reason, $order, $balanceType, $payment): WalletTransaction {
            $wallet = static::query()->lockForUpdate()->findOrFail($this->id);

            if ($wallet->bucketBalance($balanceType) < (float) $amount) {
                throw new \RuntimeException('Yetersiz cüzdan bakiyesi.');
            }

            $wallet->balance = round((float) $wallet->balance - (float) $amount, 2);
            $wallet->save();

            return $wallet->transactions()->create([
                'type' => WalletTransactionType::Debit,
                'amount' => $amount,
                'reason' => $reason,
                'balance_type' => $balanceType,
                'related_order_id' => $order?->id,
                'related_payment_id' => $payment?->id,
            ]);
        });
    }
}
