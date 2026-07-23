<?php

namespace Database\Factories;

use App\Models\FooterLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FooterLink>
 */
class FooterLinkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->words(2, true),
            'url' => '/'.fake()->slug(),
            'group' => fake()->optional()->randomElement(['Kurumsal', 'Destek', 'Yasal']),
            'sort_order' => fake()->numberBetween(0, 100),
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
