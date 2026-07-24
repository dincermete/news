<?php

namespace Database\Factories;

use App\Enums\SiteStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\BacklinkPackage>
 */
class BacklinkPackageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'competition_label' => '%50 Orta Rekabet',
            'price' => fake()->randomFloat(2, 3000, 20000),
            'currency' => 'TRY',
            'features' => [],
            'is_featured' => false,
            'status' => SiteStatus::Active,
            'sort_order' => 0,
        ];
    }
}
