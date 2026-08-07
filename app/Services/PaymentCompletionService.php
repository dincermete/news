<?php

namespace App\Services;

use App\Enums\OrderFulfillmentTrack;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SpinCreditTransactionType;
use App\Events\SuccessfulPaymentProcessed;
use App\Jobs\AwardSpinCredits;
use App\Jobs\CreditWalletTopupBalance;
use App\Jobs\InvoiceGenerationJob;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SpinCreditTransaction;
use App\Models\WalletTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentCompletionService
{
    public function complete(Payment $payment): void
    {
        $paymentId = $payment->id;

        $fresh = Payment::query()->findOrFail($paymentId);

        if ($fresh->status === PaymentStatus::Paid && $this->criticalLedgerAlreadyApplied($fresh)) {
            $this->advanceOrdersAfterPayment($fresh);

            if (! $this->hasInvoiceForPayment($fresh)) {
                $this->dispatchBestEffortEffects($fresh);
            }

            return;
        }

        DB::transaction(function () use ($paymentId): void {
            $locked = Payment::query()->whereKey($paymentId)->lockForUpdate()->firstOrFail();
            $locked->loadMissing(['order.user', 'orderGroup.user', 'orderGroup.orders', 'walletTopupPackage']);

            if ($locked->status !== PaymentStatus::Paid) {
                $locked->forceFill([
                    'status' => PaymentStatus::Paid,
                    'paid_at' => $locked->paid_at ?? now(),
                ])->save();
            }

            $this->advanceOrdersAfterPayment($locked);
            $this->ensureCriticalLedgerEffects($locked);
        });

        $this->dispatchBestEffortEffects(Payment::query()->findOrFail($paymentId));
    }

    /**
     * Ensure Instant orders are Completed and others ContentPending when allowed.
     */
    public function advanceOrdersAfterPayment(Payment $payment): void
    {
        foreach ($this->relatedOrders($payment) as $order) {
            if ($order->fulfillmentTrack() === OrderFulfillmentTrack::Instant) {
                if ($order->status === OrderStatus::PaymentPending) {
                    $order->forceCompleteInstantRecovery();
                }

                continue;
            }

            if ($order->canTransitionTo(OrderStatus::ContentPending)) {
                $order->transitionTo(OrderStatus::ContentPending);
            }
        }
    }

    public function ensureCriticalLedgerEffects(Payment $payment): void
    {
        $payment->loadMissing(['order.user', 'orderGroup.user', 'orderGroup.orders', 'walletTopupPackage']);

        (new CreditWalletTopupBalance($payment))->handle();
        (new AwardSpinCredits($payment))->handle();
    }

    public function criticalLedgerAlreadyApplied(Payment $payment): bool
    {
        $payment->loadMissing(['order', 'orderGroup.orders']);

        $topupOrders = $payment->walletTopupOrders();

        if ($topupOrders->isEmpty() && ! $payment->isWalletTopup()) {
            return true;
        }

        if ($topupOrders->isNotEmpty()) {
            foreach ($topupOrders as $order) {
                $credited = WalletTransaction::query()
                    ->where('related_order_id', $order->id)
                    ->where('reason', CreditWalletTopupBalance::REASON)
                    ->exists();

                if (! $credited) {
                    return false;
                }
            }
        }

        $expectedSpins = $this->expectedSpinCredits($payment);

        if ($expectedSpins <= 0) {
            return true;
        }

        return SpinCreditTransaction::query()
            ->where('related_payment_id', $payment->id)
            ->where('type', SpinCreditTransactionType::Credit)
            ->where('reason', 'wallet_topup')
            ->exists();
    }

    /**
     * Recovery for stuck Instant orders (backfill). Ledger first, then status.
     */
    public function recoverStuckInstantOrder(Order $order): bool
    {
        if ($order->fulfillmentTrack() !== OrderFulfillmentTrack::Instant) {
            return false;
        }

        if (! in_array($order->status, [OrderStatus::PaymentPending, OrderStatus::ContentPending], true)) {
            return false;
        }

        $payment = $this->resolvePaidPaymentForOrder($order);

        if ($payment === null) {
            return false;
        }

        return DB::transaction(function () use ($order, $payment): bool {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $lockedPayment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            $this->ensureCriticalLedgerEffects($lockedPayment);

            return $lockedOrder->forceCompleteInstantRecovery();
        });
    }

    /**
     * @return Collection<int, Order>
     */
    protected function relatedOrders(Payment $payment): Collection
    {
        $payment->loadMissing(['order', 'orderGroup.orders']);

        if ($payment->order_id !== null && $payment->order !== null) {
            return collect([$payment->order]);
        }

        return ($payment->orderGroup?->orders ?? collect())->values();
    }

    protected function expectedSpinCredits(Payment $payment): int
    {
        return (new AwardSpinCredits($payment))->expectedCredits();
    }

    protected function dispatchBestEffortEffects(Payment $payment): void
    {
        $run = function () use ($payment): void {
            InvoiceGenerationJob::dispatch($payment);
            SuccessfulPaymentProcessed::dispatch($payment);
        };

        if (DB::transactionLevel() > 0) {
            DB::afterCommit($run);

            return;
        }

        $run();
    }

    protected function hasInvoiceForPayment(Payment $payment): bool
    {
        if ($payment->order_group_id !== null) {
            return Invoice::query()->where('order_group_id', $payment->order_group_id)->exists();
        }

        if ($payment->order_id !== null) {
            return Invoice::query()
                ->where('order_id', $payment->order_id)
                ->whereNull('order_group_id')
                ->exists();
        }

        return false;
    }

    protected function resolvePaidPaymentForOrder(Order $order): ?Payment
    {
        $order->loadMissing(['payments', 'orderGroup.payments']);

        $fromOrder = $order->payments
            ->first(fn (Payment $payment): bool => $payment->status === PaymentStatus::Paid);

        if ($fromOrder !== null) {
            return $fromOrder;
        }

        return $order->orderGroup?->payments
            ->first(fn (Payment $payment): bool => $payment->status === PaymentStatus::Paid);
    }
}
