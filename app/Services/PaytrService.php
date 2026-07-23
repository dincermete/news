<?php

namespace App\Services;

use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\Payment;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PaytrService
{
    public function getIframeToken(Payment $payment, ?string $userIp = null): array
    {
        $payment->loadMissing(['order.user', 'order.site', 'orderGroup.user', 'orderGroup.orders.site']);

        $merchantId = (string) config('paytr.merchant_id');
        $merchantKey = (string) config('paytr.merchant_key');
        $merchantSalt = (string) config('paytr.merchant_salt');

        if ($merchantId === '' || $merchantKey === '' || $merchantSalt === '') {
            throw new RuntimeException('PayTR merchant bilgileri yapılandırılmamış.');
        }

        [$amount, $currency, $email, $userName, $basketRows, $merchantOid] = $this->resolvePaymentContext($payment);

        $paymentAmount = (string) ((int) round($amount * 100));
        $userBasket = base64_encode(json_encode($basketRows, JSON_THROW_ON_ERROR));

        $userIp ??= request()->ip() ?? '127.0.0.1';
        $noInstallment = (string) config('paytr.no_installment', 0);
        $maxInstallment = (string) config('paytr.max_installment', 0);
        $paytrCurrency = $this->toPaytrCurrency($currency);
        $testMode = (string) config('paytr.test_mode', '1');

        $hashStr = $merchantId.$userIp.$merchantOid.$email.$paymentAmount.$userBasket.$noInstallment.$maxInstallment.$paytrCurrency.$testMode;
        $paytrToken = base64_encode(hash_hmac('sha256', $hashStr.$merchantSalt, $merchantKey, true));

        $payload = [
            'merchant_id' => $merchantId,
            'user_ip' => $userIp,
            'merchant_oid' => $merchantOid,
            'email' => $email,
            'payment_amount' => $paymentAmount,
            'paytr_token' => $paytrToken,
            'user_basket' => $userBasket,
            'debug_on' => (string) config('paytr.debug_on', 1),
            'no_installment' => $noInstallment,
            'max_installment' => $maxInstallment,
            'user_name' => $userName,
            'user_address' => 'Adres belirtilmedi',
            'user_phone' => '05000000000',
            'merchant_ok_url' => (string) config('paytr.ok_url'),
            'merchant_fail_url' => (string) config('paytr.fail_url'),
            'timeout_limit' => (string) config('paytr.timeout_limit', 30),
            'currency' => $paytrCurrency,
            'test_mode' => $testMode,
        ];

        try {
            $response = Http::asForm()
                ->timeout(20)
                ->post((string) config('paytr.token_url'), $payload)
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            throw new RuntimeException('PayTR token isteği başarısız: '.$exception->getMessage(), previous: $exception);
        }

        if (($response['status'] ?? null) !== 'success' || blank($response['token'] ?? null)) {
            throw new RuntimeException('PayTR token alınamadı: '.($response['reason'] ?? 'bilinmeyen hata'));
        }

        $payment->forceFill([
            'paytr_merchant_oid' => $merchantOid,
            'paytr_token' => $response['token'],
            'amount' => $amount,
            'currency' => $currency,
            'method' => PaymentMethod::Card,
            'status' => PaymentStatus::Pending,
        ])->save();

        return [
            'token' => $response['token'],
            'merchant_oid' => $merchantOid,
            'payment' => $payment,
        ];
    }

    public function verifyCallbackHash(array $payload): bool
    {
        $merchantKey = (string) config('paytr.merchant_key');
        $merchantSalt = (string) config('paytr.merchant_salt');

        $expected = base64_encode(hash_hmac(
            'sha256',
            ($payload['merchant_oid'] ?? '').$merchantSalt.($payload['status'] ?? '').($payload['total_amount'] ?? ''),
            $merchantKey,
            true,
        ));

        return hash_equals($expected, (string) ($payload['hash'] ?? ''));
    }

    /**
     * @return array{0: float, 1: Currency|string|null, 2: string, 3: string, 4: list<array{0: string, 1: string, 2: int}>, 5: string}
     */
    protected function resolvePaymentContext(Payment $payment): array
    {
        if ($payment->order_id !== null) {
            $order = $payment->order;

            if (! $order instanceof Order) {
                throw new RuntimeException('Payment siparişi bulunamadı.');
            }

            $amount = (float) ($payment->amount ?: $order->price);
            $currency = $payment->currency ?? $order->currency ?? Currency::Try;
            $merchantOid = $payment->paytr_merchant_oid ?? $this->generateMerchantOidForOrder($order);

            return [
                $amount,
                $currency,
                $order->user?->email ?? 'musteri@example.com',
                $order->user?->name ?? 'Musteri',
                [[$order->site?->domain ?? 'Sipariş #'.$order->id, number_format($amount, 2, '.', ''), 1]],
                $merchantOid,
            ];
        }

        $orderGroup = $payment->orderGroup;

        if (! $orderGroup instanceof OrderGroup) {
            throw new RuntimeException('Payment sipariş grubu bulunamadı.');
        }

        $amount = (float) ($payment->amount ?: $orderGroup->total);
        $currency = $payment->currency ?? $orderGroup->currency ?? Currency::Try;
        $merchantOid = $payment->paytr_merchant_oid ?? $this->generateMerchantOidForOrderGroup($orderGroup);

        $basketRows = $orderGroup->orders
            ->map(fn (Order $order): array => [
                $order->site?->domain ?? 'Sipariş #'.$order->id,
                number_format((float) $order->price, 2, '.', ''),
                1,
            ])
            ->values()
            ->all();

        if ($basketRows === []) {
            $basketRows = [['Sipariş grubu #'.$orderGroup->id, number_format($amount, 2, '.', ''), 1]];
        }

        return [
            $amount,
            $currency,
            $orderGroup->user?->email ?? 'musteri@example.com',
            $orderGroup->user?->name ?? 'Musteri',
            $basketRows,
            $merchantOid,
        ];
    }

    protected function generateMerchantOidForOrder(Order $order): string
    {
        return 'ORD'.$order->id.Str::upper(Str::random(8));
    }

    protected function generateMerchantOidForOrderGroup(OrderGroup $orderGroup): string
    {
        return 'GRP'.$orderGroup->id.Str::upper(Str::random(8));
    }

    protected function toPaytrCurrency(Currency|string|null $currency): string
    {
        $value = $currency instanceof Currency ? $currency->value : (string) $currency;

        return match ($value) {
            'TRY', 'TL' => 'TL',
            'USD' => 'USD',
            'EUR' => 'EUR',
            default => 'TL',
        };
    }
}
