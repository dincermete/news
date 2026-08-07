<?php

namespace App\Services;

use App\Enums\Currency;
use App\Enums\OrderFulfillmentTrack;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SpinCreditTransactionType;
use App\Enums\WalletBalanceType;
use App\Jobs\CreditWalletTopupBalance;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SpinCreditTransaction;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InstantOrderRefundService
{
    public const WALLET_REFUND_REASON = 'wallet_topup_refund';

    public const SPIN_REFUND_REASON = 'wallet_topup_refund';

    public function refund(Order $order): void
    {
        if ($order->fulfillmentTrack() !== OrderFulfillmentTrack::Instant) {
            throw new RuntimeException('Bu iade yalnızca bakiye (Instant) siparişleri içindir.');
        }

        if ($order->status !== OrderStatus::Completed) {
            throw new RuntimeException('Yalnızca tamamlanmış bakiye siparişleri iade edilebilir.');
        }

        DB::transaction(function () use ($order): void {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $locked->loadMissing(['user', 'payments', 'orderGroup.payments']);

            if ($locked->status !== OrderStatus::Completed) {
                throw new RuntimeException('Sipariş iade için uygun değil.');
            }

            $this->clawbackWallet($locked);
            $this->clawbackSpinCredits($locked);

            $locked->forceFill(['status' => OrderStatus::Refunded])->save();
        });
    }

    protected function clawbackWallet(Order $order): void
    {
        $already = WalletTransaction::query()
            ->where('related_order_id', $order->id)
            ->where('reason', self::WALLET_REFUND_REASON)
            ->exists();

        if ($already) {
            return;
        }

        $credit = WalletTransaction::query()
            ->where('related_order_id', $order->id)
            ->where('reason', CreditWalletTopupBalance::REASON)
            ->first();

        if ($credit === null) {
            return;
        }

        $currency = $order->currency instanceof Currency
            ? $order->currency
            : Currency::Try;

        $wallet = Wallet::forUser($order->user, $currency);
        $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

        $amount = (float) $credit->amount;

        if ($wallet->bucketBalance(WalletBalanceType::Main) + 0.00001 < $amount) {
            throw new RuntimeException(
                'Cüzdan bakiyesi iade için yetersiz. Kullanıcı bakiyeyi harcamış olabilir; önce bakiyeyi kontrol edin.'
            );
        }

        $payment = $this->resolvePayment($order);

        $wallet->debit(
            amount: $amount,
            reason: self::WALLET_REFUND_REASON,
            order: $order,
            balanceType: WalletBalanceType::Main,
            payment: $payment,
        );
    }

    protected function clawbackSpinCredits(Order $order): void
    {
        $payment = $this->resolvePayment($order);

        if ($payment === null) {
            return;
        }

        $already = SpinCreditTransaction::query()
            ->where('related_payment_id', $payment->id)
            ->where('type', SpinCreditTransactionType::Debit)
            ->where('reason', self::SPIN_REFUND_REASON)
            ->exists();

        if ($already) {
            return;
        }

        $credit = SpinCreditTransaction::query()
            ->where('related_payment_id', $payment->id)
            ->where('type', SpinCreditTransactionType::Credit)
            ->where('reason', 'wallet_topup')
            ->first();

        if ($credit === null || (int) $credit->amount <= 0) {
            return;
        }

        $user = $order->user;
        $available = $user->spinCreditBalance();

        if ($available < (int) $credit->amount) {
            throw new RuntimeException(
                'Çark kredisi iade için yetersiz. Kullanıcı kredileri harcamış olabilir.'
            );
        }

        SpinCreditTransaction::query()->create([
            'user_id' => $user->id,
            'type' => SpinCreditTransactionType::Debit,
            'amount' => (int) $credit->amount,
            'reason' => self::SPIN_REFUND_REASON,
            'related_payment_id' => $payment->id,
        ]);
    }

    protected function resolvePayment(Order $order): ?Payment
    {
        $order->loadMissing(['payments', 'orderGroup.payments', 'user']);

        $paid = $order->payments
            ->first(fn (Payment $payment): bool => $payment->status === PaymentStatus::Paid);

        if ($paid !== null) {
            return $paid;
        }

        return $order->orderGroup?->payments
            ->first(fn (Payment $payment): bool => $payment->status === PaymentStatus::Paid);
    }
}
