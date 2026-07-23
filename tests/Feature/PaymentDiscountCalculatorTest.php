<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Services\PaymentDiscountCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentDiscountCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_transfer_applies_configured_percent_discount(): void
    {
        config(['payment.bank_transfer_discount_percent' => 2]);

        $group = OrderGroup::factory()->create([
            'total' => 250,
            'currency' => Currency::Try,
        ]);

        $final = app(PaymentDiscountCalculator::class)
            ->calculateFinalAmount($group, PaymentMethod::BankTransfer);

        $this->assertSame(245.0, $final);
    }

    public function test_other_methods_do_not_apply_discount(): void
    {
        config(['payment.bank_transfer_discount_percent' => 2]);

        $group = OrderGroup::factory()->create([
            'total' => 250,
            'currency' => Currency::Try,
        ]);

        $calculator = app(PaymentDiscountCalculator::class);

        $this->assertSame(250.0, $calculator->calculateFinalAmount($group, PaymentMethod::Card));
        $this->assertSame(250.0, $calculator->calculateFinalAmount($group, PaymentMethod::Balance));
    }

    public function test_single_order_bank_transfer_uses_same_discount(): void
    {
        config(['payment.bank_transfer_discount_percent' => 2]);

        $order = Order::factory()->create(['price' => 100]);

        $final = app(PaymentDiscountCalculator::class)
            ->calculateForOrder($order, PaymentMethod::BankTransfer);

        $this->assertSame(98.0, $final);
    }
}
