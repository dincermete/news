<?php

namespace App\Jobs;

use App\Enums\SpinCreditTransactionType;
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
        $payment = $this->payment->loadMissing(['order.user', 'walletTopupPackage']);

        $credits = $this->resolveCredits($payment);

        if ($credits <= 0) {
            return;
        }

        $userId = $payment->order?->user_id;

        if ($userId === null) {
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

    protected function resolveCredits(Payment $payment): int
    {
        if ($payment->wallet_topup_package_id && $payment->walletTopupPackage) {
            return (int) $payment->walletTopupPackage->spin_credits;
        }

        if ($payment->custom_topup_amount !== null) {
            return (int) floor(((float) $payment->custom_topup_amount) / 100) * 3;
        }

        return 0;
    }
}
