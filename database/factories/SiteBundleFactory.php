<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\SiteStatus;
use App\Models\SiteBundle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteBundle>
 */
class SiteBundleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'price' => fake()->randomFloat(2, 100, 2000),
            'currency' => Currency::Try,
            'status' => SiteStatus::Active,
        ];
    }
}
