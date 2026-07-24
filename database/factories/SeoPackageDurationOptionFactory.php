<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\SeoPackageDurationOption>
 */
class SeoPackageDurationOptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Aylık',
            'months' => 1,
            'price_multiplier' => 1,
            'bonus_label' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
