<?php

namespace Database\Factories;

use App\Models\FooterLinkDurationOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FooterLinkDurationOption>
 */
class FooterLinkDurationOptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['1 Aylık', '3 Aylık', '6 Aylık', '12 Aylık']),
            'months' => fake()->randomElement([1, 3, 6, 12]),
            'price_multiplier' => fake()->randomFloat(4, 0.5, 2),
            'flat_price' => null,
            'is_active' => true,
        ];
    }

    public function flat(float $price): static
    {
        return $this->state(fn (array $attributes): array => [
            'flat_price' => $price,
            'price_multiplier' => null,
        ]);
    }
}
