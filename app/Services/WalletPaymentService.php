<?php

namespace App\Services;

use App\Enums\Currency;
use App\Enums\PaymentStatus;
use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionType;
use App\Exceptions\InsufficientWalletBalanceException;
use App\Jobs\ProcessSuccessfulPayment;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletPaymentService
{
    public function payWithWallet(Payment $payment): void
    {
        if ($payment->status === PaymentStatus::Paid) {
            return;
        }

        $payment->loadMissing(['order.user', 'orderGroup.user']);

        $user = $this->resolveUser($payment);
        $currency = $payment->currency instanceof Currency
            ? $payment->currency
            : Currency::Try;

        DB::transaction(function () use ($payment, $user, $currency): void {
            $wallet = Wallet::forUser($user, $currency);
            $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            $amountDue = round((float) $payment->amount, 2);

            if ($wallet->totalAvailableBalance() + 0.00001 < $amountDue) {
                throw InsufficientWalletBalanceException::make();
            }

            $remaining = $amountDue;
            $relatedOrderId = $payment->order_id;

            foreach (WalletBalanceType::debitPriority() as $bucket) {
                if ($remaining <= 0) {
                    break;
                }

                $available = $wallet->bucketBalance($bucket);

                if ($available <= 0) {
                    continue;
                }

                $take = min($available, $remaining);

                if ($take <= 0) {
                    continue;
                }

                $wallet->balance = round((float) $wallet->balance - $take, 2);
                $wallet->save();

                $wallet->transactions()->create([
                    'type' => WalletTransactionType::Debit,
                    'amount' => $take,
                    'reason' => 'payment',
                    'balance_type' => $bucket,
                    'related_order_id' => $relatedOrderId,
                    'related_payment_id' => $payment->id,
                ]);

                $remaining = round($remaining - $take, 2);
            }

            if ($remaining > 0.00001) {
                throw InsufficientWalletBalanceException::make();
            }

            $payment->forceFill([
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
            ])->save();

            $payment->loadMissing(['order', 'orderGroup.orders']);
            $payment->markRelatedOrdersContentPending();
        });

        ProcessSuccessfulPayment::dispatch($payment->fresh());
    }

    protected function resolveUser(Payment $payment): User
    {
        $user = $payment->order?->user ?? $payment->orderGroup?->user;

        if (! $user instanceof User) {
            throw new RuntimeException('Ödeme için kullanıcı bulunamadı.');
        }

        return $user;
    }
}
