<?php

namespace Database\Factories;

use App\Enums\SiteStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\SeoPackage>
 */
class SeoPackageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'keyword_count' => fake()->numberBetween(10, 40),
            'monthly_price' => fake()->randomFloat(2, 20000, 50000),
            'currency' => 'TRY',
            'features' => [],
            'is_featured' => false,
            'status' => SiteStatus::Active,
            'sort_order' => 0,
        ];
    }
}
