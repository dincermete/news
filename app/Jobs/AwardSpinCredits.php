<?php

namespace App\Jobs;

use App\Enums\SpinCreditTransactionType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SpinCreditTransaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AwardSpinCredits implements ShouldQueue
{
    use Queueable;

    public function __construct(public Payment $payment) {}

    public function handle(): void
    {
        $payment = $this->payment;
        $orders = $payment->walletTopupOrders();

        $userId = $payment->order?->user_id ?? $payment->orderGroup?->user_id;

        if ($userId === null) {
            return;
        }

        $credits = $orders->isNotEmpty()
            ? $orders->sum(fn (Order $order): int => $this->resolveOrderCredits($order))
            : $this->resolveLegacyCredits($payment);

        $credits = (int) $credits;

        if ($credits <= 0) {
            return;
        }

        $alreadyAwarded = SpinCreditTransaction::query()
            ->where('related_payment_id', $payment->id)
            ->where('type', SpinCreditTransactionType::Credit)
            ->where('reason', 'wallet_topup')
            ->exists();

        if ($alreadyAwarded) {
            return;
        }

        SpinCreditTransaction::query()->create([
            'user_id' => $userId,
            'type' => SpinCreditTransactionType::Credit,
            'amount' => $credits,
            'reason' => 'wallet_topup',
            'related_payment_id' => $payment->id,
        ]);
    }

    protected function resolveOrderCredits(Order $order): int
    {
        if ($order->wallet_topup_package_id && $order->walletTopupPackage) {
            return (int) $order->walletTopupPackage->spin_credits;
        }

        return (int) floor(((float) $order->price) / 100) * 3;
    }

    protected function resolveLegacyCredits(Payment $payment): int
    {
        $payment->loadMissing('walletTopupPackage');

        if ($payment->wallet_topup_package_id && $payment->walletTopupPackage) {
            return (int) $payment->walletTopupPackage->spin_credits;
        }

        if ($payment->custom_topup_amount !== null) {
            return (int) floor(((float) $payment->custom_topup_amount) / 100) * 3;
        }

        return 0;
    }
}
