<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\OrderGroup;

class PaymentDiscountCalculator
{
    public function calculateFinalAmount(OrderGroup $orderGroup, PaymentMethod $method): float
    {
        return $this->applyDiscount((float) $orderGroup->total, $method);
    }

    public function calculateForOrder(Order $order, PaymentMethod $method): float
    {
        return $this->applyDiscount((float) $order->price, $method);
    }

    public function applyDiscount(float $total, PaymentMethod $method): float
    {
        if ($method !== PaymentMethod::BankTransfer) {
            return round($total, 2);
        }

        $percent = (float) config('payment.bank_transfer_discount_percent', 0);

        if ($percent <= 0) {
            return round($total, 2);
        }

        $discount = round($total * ($percent / 100), 2);

        return max(0, round($total - $discount, 2));
    }
}
