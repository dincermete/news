<?php

namespace App\Listeners;

use App\Enums\Currency;
use App\Events\WalletRefundRequested;
use App\Models\Wallet;
use Illuminate\Contracts\Queue\ShouldQueue;

class RefundToWallet implements ShouldQueue
{
    public function handle(WalletRefundRequested $event): void
    {
        $order = $event->order->loadMissing('user');

        $currency = $order->currency instanceof Currency
            ? $order->currency
            : Currency::Try;

        $wallet = Wallet::forUser($order->user, $currency);

        $wallet->credit(
            amount: (float) $order->price,
            reason: 'order_refund',
            order: $order,
        );
    }
}
