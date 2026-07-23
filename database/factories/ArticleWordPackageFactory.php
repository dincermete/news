<?php

namespace Database\Factories;

use App\Models\ArticleWordPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleWordPackage>
 */
class ArticleWordPackageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'word_count' => fake()->unique()->numberBetween(50, 5000),
            'price' => fake()->randomFloat(2, 15, 150),
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
