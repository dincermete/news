<?php

namespace Database\Factories;

use App\Models\WalletTopupPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WalletTopupPackage>
 */
class WalletTopupPackageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->randomElement([50, 100, 200, 500, 1000]),
            'spin_credits' => fake()->numberBetween(1, 50),
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
