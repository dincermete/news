<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\SiteStatus;
use App\Models\SiteBundle;
use App\Support\BundleIconOptions;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $name = fake()->words(3, true);
        $icon = fake()->randomElement(array_keys(BundleIconOptions::labels()));
        $palette = BundleIconOptions::paletteForIcon($icon);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->optional()->sentence(),
            'icon' => $icon,
            'bg_color_from' => $palette['from'],
            'bg_color_to' => $palette['to'],
            'price' => fake()->randomFloat(2, 100, 2000),
            'currency' => Currency::Try,
            'status' => SiteStatus::Active,
        ];
    }
}
