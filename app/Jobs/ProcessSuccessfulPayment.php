<?php

namespace App\Jobs;

use App\Events\SuccessfulPaymentProcessed;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessSuccessfulPayment implements ShouldQueue
{
    use Queueable;

    public function __construct(public Payment $payment) {}

    public function handle(): void
    {
        $this->payment->loadMissing(['order.user', 'orderGroup.user', 'walletTopupPackage']);

        InvoiceGenerationJob::dispatchSync($this->payment);
        AwardSpinCredits::dispatchSync($this->payment);
        SuccessfulPaymentProcessed::dispatch($this->payment);
    }
}
