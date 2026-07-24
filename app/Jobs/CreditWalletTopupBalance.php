<?php

namespace App\Jobs;

use App\Enums\WalletBalanceType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreditWalletTopupBalance implements ShouldQueue
{
    use Queueable;

    public const REASON = 'wallet_topup';

    public function __construct(public Payment $payment) {}

    public function handle(): void
    {
        $payment = $this->payment;
        $orders = $payment->walletTopupOrders();

        foreach ($orders as $order) {
            $this->creditOrder($order, $payment);
        }
    }

    protected function creditOrder(Order $order, Payment $payment): void
    {
        $alreadyCredited = WalletTransaction::query()
            ->where('related_order_id', $order->id)
            ->where('reason', self::REASON)
            ->exists();

        if ($alreadyCredited) {
            return;
        }

        $user = $order->user ?? $payment->orderGroup?->user;

        if ($user === null) {
            return;
        }

        $wallet = Wallet::forUser($user, $order->currency);

        $wallet->credit(
            amount: (float) $order->price,
            reason: self::REASON,
            order: $order,
            balanceType: WalletBalanceType::Main,
            payment: $payment,
        );
    }
}
