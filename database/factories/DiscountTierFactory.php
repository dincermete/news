<?php

namespace Database\Factories;

use App\Models\DiscountTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscountTier>
 */
class DiscountTierFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'min_cart_amount' => fake()->randomElement([500, 1000, 2000, 5000]),
            'discount_percentage' => fake()->randomElement([5, 10, 15, 20]),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
