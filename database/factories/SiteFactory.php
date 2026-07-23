<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\MetricSource;
use App\Enums\SiteStatus;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Support\SiteSeoMetrics;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $attributes = [
            'domain' => fake()->unique()->domainName(),
            'site_category_id' => SiteCategory::factory(),
            'description' => fake()->optional()->paragraph(),
            'age' => fake()->numberBetween(1, 25),
            'is_dofollow' => fake()->boolean(80),
            'is_news_approved' => fake()->boolean(30),
            'status' => fake()->randomElement(SiteStatus::cases()),
            'price' => fake()->randomFloat(2, 10, 500),
            'discount_price' => fake()->optional(0.3)->randomFloat(2, 5, 400),
            'currency' => fake()->randomElement(Currency::cases()),
            'daily_capacity' => fake()->optional()->numberBetween(1, 20),
            'weekly_capacity' => fake()->optional()->numberBetween(5, 100),
            'internal_notes' => fake()->optional()->sentence(),
            'site_owner_name' => fake()->optional()->name(),
            'site_owner_contact' => fake()->optional()->email(),
            'site_owner_payment_info' => fake()->optional()->sentence(),
        ];

        foreach (SiteSeoMetrics::keys() as $metric) {
            $attributes["{$metric}_value"] = fake()->optional(0.7)->randomFloat(2, 0, 100);
            $attributes["{$metric}_source"] = MetricSource::Manual;
            $attributes["{$metric}_updated_at"] = fake()->optional(0.5)->dateTimeBetween('-1 year');
        }

        return $attributes;
    }
}
