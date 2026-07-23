<?php

namespace App\Observers;

use App\Exceptions\InvalidPaymentTargetException;
use App\Models\Payment;

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
}
