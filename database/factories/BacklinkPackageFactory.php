<?php

namespace Database\Factories;

use App\Enums\SiteStatus;
use App\Models\BacklinkPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BacklinkPackage>
 */
class BacklinkPackageFactory extends Factory
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
