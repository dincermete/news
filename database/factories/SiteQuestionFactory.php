<?php

namespace Database\Factories;

use App\Models\BacklinkPackage;
use App\Models\SeoPackage;
use App\Models\Site;
use App\Models\SiteBundle;
use App\Models\SiteQuestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteQuestion>
 */
class SiteQuestionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'site_bundle_id' => null,
            'user_id' => User::factory(),
            'guest_email' => null,
            'question' => fake()->sentence().'?',
            'answer' => null,
            'answered_by' => null,
            'answered_at' => null,
            'is_public' => true,
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

    public function guest(?string $email = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => null,
            'guest_email' => $email ?? fake()->safeEmail(),
        ]);
    }

    public function answered(?User $admin = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'answer' => fake()->paragraph(),
            'answered_by' => $admin?->id ?? User::factory(),
            'answered_at' => now(),
            'is_public' => true,
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_public' => false,
        ]);
    }
}
