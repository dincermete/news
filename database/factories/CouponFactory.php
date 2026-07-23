<?php

namespace Database\Factories;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::upper(Str::random(8)),
            'type' => CouponType::Percentage,
            'value' => fake()->randomElement([5, 10, 15, 20]),
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addMonth(),
            'usage_limit' => 100,
            'used_count' => 0,
            'min_cart_amount' => null,
            'is_active' => true,
        ];
    }

    public function fixed(float $amount): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CouponType::FixedAmount,
            'value' => $amount,
        ]);
    }

    public function percentage(float $percent): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CouponType::Percentage,
            'value' => $percent,
        ]);
    }
}
