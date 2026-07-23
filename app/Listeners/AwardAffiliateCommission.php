<?php

namespace App\Listeners;

use App\Enums\AffiliateCommissionStatus;
use App\Enums\Currency;
use App\Enums\WalletBalanceType;
use App\Events\SuccessfulPaymentProcessed;
use App\Models\AffiliateCommission;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AwardAffiliateCommission
{
    public function handle(SuccessfulPaymentProcessed $event): void
    {
        $payment = $event->payment->loadMissing([
            'order.user',
            'orderGroup.orders.user',
        ]);

        if ($payment->isWalletTopup()) {
            return;
        }

        foreach ($this->ordersForPayment($payment) as $order) {
            $this->awardForOrder($order, $payment);
        }
    }

    /**
     * @return Collection<int, Order>
     */
    protected function ordersForPayment(Payment $payment): Collection
    {
        if ($payment->order !== null) {
            return collect([$payment->order]);
        }

        return collect($payment->orderGroup?->orders ?? []);
    }

    protected function awardForOrder(Order $order, Payment $payment): void
    {
        $buyer = $order->user;

        if ($buyer === null || $buyer->referred_by_id === null) {
            return;
        }

        $referrer = User::query()->find($buyer->referred_by_id);

        if ($referrer === null || $referrer->affiliate_commission_rate === null) {
            return;
        }

        if (AffiliateCommission::query()->where('order_id', $order->id)->exists()) {
            return;
        }

        $amount = round(
            (float) $order->price * (float) $referrer->affiliate_commission_rate / 100,
            2,
        );

        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($referrer, $buyer, $order, $payment, $amount): void {
            AffiliateCommission::query()->create([
                'referrer_id' => $referrer->id,
                'referred_user_id' => $buyer->id,
                'order_id' => $order->id,
                'amount' => $amount,
                'status' => AffiliateCommissionStatus::Approved,
            ]);

            Wallet::forUser($referrer, Currency::Try)->credit(
                amount: $amount,
                reason: 'affiliate_commission',
                order: $order,
                balanceType: WalletBalanceType::AffiliateCommission,
                payment: $payment,
            );
        });
    }
}
