<?php

namespace Database\Factories;

use App\Models\BacklinkPackage;
use App\Models\SeoPackage;
use App\Models\Site;
use App\Models\SiteBundle;
use App\Models\SiteReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteReview>
 */
class SiteReviewFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'site_bundle_id' => null,
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('05#########'),
            'message' => fake()->paragraph(),
            'is_approved' => false,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    public function forBundle(?SiteBundle $bundle = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'site_id' => null,
            'site_bundle_id' => $bundle?->id ?? SiteBundle::factory(),
        ]);
    }

    public function forSeoPackage(?SeoPackage $package = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'site_id' => null,
            'seo_package_id' => $package?->id ?? SeoPackage::factory(),
        ]);
    }

    public function forBacklinkPackage(?BacklinkPackage $package = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'site_id' => null,
            'backlink_package_id' => $package?->id ?? BacklinkPackage::factory(),
        ]);
    }

    public function approved(?User $admin = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_approved' => true,
            'approved_by' => $admin?->id ?? User::factory(),
            'approved_at' => now(),
        ]);
    }
}
