<?php

namespace Database\Factories;

use App\Enums\AffiliateCommissionStatus;
use App\Models\AffiliateCommission;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AffiliateCommission>
 */
class AffiliateCommissionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'referrer_id' => User::factory()->customer(),
            'referred_user_id' => User::factory()->customer(),
            'order_id' => Order::factory(),
            'amount' => fake()->randomFloat(2, 5, 100),
            'status' => AffiliateCommissionStatus::Approved,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => AffiliateCommissionStatus::Pending,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => AffiliateCommissionStatus::Approved,
        ]);
    }
}
