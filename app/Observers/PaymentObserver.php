<?php

namespace App\Observers;

use App\Enums\PaymentMethod;
use App\Exceptions\InvalidPaymentTargetException;
use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentObserver
{
    public function saving(Payment $payment): void
    {
        $hasOrder = filled($payment->order_id);
        $hasOrderGroup = filled($payment->order_group_id);

        if ($hasOrder === $hasOrderGroup) {
            throw InvalidPaymentTargetException::make(
                'Payment kaydında order_id veya order_group_id alanlarından tam olarak biri dolu olmalıdır.',
            );
        }
    }

    public function creating(Payment $payment): void
    {
        if ($payment->method === PaymentMethod::BankTransfer && blank($payment->reference_code)) {
            $payment->reference_code = $this->generateReferenceCode();
        }
    }

    protected function generateReferenceCode(): string
    {
        do {
            $code = 'NT-'.mb_strtoupper(Str::random(6));
        } while (Payment::query()->where('reference_code', $code)->exists());

        return $code;
    }
}
