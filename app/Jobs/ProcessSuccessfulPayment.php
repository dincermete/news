<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\PaymentCompletionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Orphan-queue safety net: delegates to PaymentCompletionService.
 * New code should call PaymentCompletionService::complete() directly.
 */
class ProcessSuccessfulPayment implements ShouldQueue
{
    use Queueable;

    public function __construct(public Payment $payment) {}

    public function handle(?PaymentCompletionService $completion = null): void
    {
        $completion ??= app(PaymentCompletionService::class);
        $completion->complete($this->payment);
    }
}
