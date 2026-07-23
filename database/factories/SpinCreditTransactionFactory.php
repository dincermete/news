<?php

namespace Database\Factories;

use App\Enums\SpinCreditTransactionType;
use App\Models\SpinCreditTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpinCreditTransaction>
 */
class SpinCreditTransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => SpinCreditTransactionType::Credit,
            'amount' => fake()->numberBetween(1, 10),
            'reason' => 'wallet_topup',
            'related_payment_id' => null,
        ];
    }

    public function credit(int $amount = 1): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => SpinCreditTransactionType::Credit,
            'amount' => $amount,
        ]);
    }

    public function debit(int $amount = 1): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => SpinCreditTransactionType::Debit,
            'amount' => $amount,
            'reason' => 'spin_wheel_spin',
        ]);
    }
}
