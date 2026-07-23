<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Observers\PaymentObserver;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([PaymentObserver::class])]
#[Fillable([
    'order_id',
    'order_group_id',
    'amount',
    'currency',
    'method',
    'status',
    'paytr_merchant_oid',
    'paytr_token',
    'paid_at',
    'receipt_path',
    'bank_name',
    'payer_name',
    'payer_note',
    'wallet_topup_package_id',
    'custom_topup_amount',
])]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
        'currency' => 'TRY',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'currency' => Currency::class,
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'paid_at' => 'datetime',
            'custom_topup_amount' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderGroup(): BelongsTo
    {
        return $this->belongsTo(OrderGroup::class);
    }

    public function walletTopupPackage(): BelongsTo
    {
        return $this->belongsTo(WalletTopupPackage::class);
    }

    public function isPendingBankTransfer(): bool
    {
        return $this->method === PaymentMethod::BankTransfer
            && $this->status === PaymentStatus::Notified;
    }

    public function isWalletTopup(): bool
    {
        return $this->wallet_topup_package_id !== null
            || $this->custom_topup_amount !== null;
    }

    public function markRelatedOrdersContentPending(): void
    {
        if ($this->order_id !== null) {
            $this->loadMissing('order');

            $order = $this->order;

            if ($order && $order->canTransitionTo(OrderStatus::ContentPending)) {
                $order->transitionTo(OrderStatus::ContentPending);
            }

            return;
        }

        if ($this->order_group_id === null) {
            return;
        }

        $this->loadMissing('orderGroup.orders');

        foreach ($this->orderGroup?->orders ?? [] as $order) {
            if ($order->canTransitionTo(OrderStatus::ContentPending)) {
                $order->transitionTo(OrderStatus::ContentPending);
            }
        }
    }
}
