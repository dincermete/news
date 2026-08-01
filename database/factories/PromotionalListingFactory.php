<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Models\PromotionalListing;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromotionalListing>
 */
class PromotionalListingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 10, 500);

        return [
            'site_id' => Site::factory()->withoutListings(),
            'type' => PromotionalListingType::SiteArticle,
            'price' => $price,
            'discount_price' => fake()->optional(0.3)->randomFloat(2, 5, max(5, $price - 1)),
            'currency' => Currency::Try,
            'status' => SiteStatus::Active,
            'short_description' => fake()->optional()->sentence(),
            'description' => fake()->optional()->paragraph(),
        ];
    }

    public function article(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => PromotionalListingType::SiteArticle,
        ]);
    }

    public function pressRelease(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => PromotionalListingType::PressRelease,
            'discount_price' => null,
        ]);
    }

    public function footerLink(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => PromotionalListingType::FooterLink,
            'discount_price' => null,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SiteStatus::Active,
        ]);
    }
}
