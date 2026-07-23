<?php

namespace Database\Factories;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionType;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WalletTransaction>
 */
class WalletTransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'type' => WalletTransactionType::Credit,
            'amount' => fake()->randomFloat(2, 10, 200),
            'reason' => fake()->randomElement(['order_refund', 'manual_topup', 'order_payment']),
            'balance_type' => WalletBalanceType::Main,
            'related_order_id' => null,
            'related_payment_id' => null,
        ];
    }

    public function credit(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => WalletTransactionType::Credit,
        ]);
    }

    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes): array => [
            'related_order_id' => $order->id,
            'amount' => $order->price,
        ]);
    }
}
