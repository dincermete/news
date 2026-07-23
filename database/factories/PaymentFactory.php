<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount' => fake()->randomFloat(2, 50, 500),
            'currency' => Currency::Try,
            'method' => PaymentMethod::Card,
            'status' => PaymentStatus::Pending,
            'paytr_merchant_oid' => 'ORD'.Str::upper(Str::random(10)),
            'paytr_token' => null,
            'paid_at' => null,
            'receipt_path' => null,
        ];
    }

    public function pendingBankTransfer(): static
    {
        return $this->state(fn (array $attributes): array => [
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::Notified,
            'receipt_path' => 'receipts/sample.pdf',
            'paytr_merchant_oid' => null,
            'bank_name' => 'Ziraat Bankası',
            'payer_name' => fake()->name(),
            'payer_note' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);
    }
}
