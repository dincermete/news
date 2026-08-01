<?php

namespace Database\Factories;

use App\Enums\SiteStatus;
use App\Models\SeoPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SeoPackage>
 */
class SeoPackageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
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
