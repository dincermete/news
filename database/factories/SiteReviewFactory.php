<?php

namespace Database\Factories;

use App\Models\Site;
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

    public function approved(?User $admin = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_approved' => true,
            'approved_by' => $admin?->id ?? User::factory(),
            'approved_at' => now(),
        ]);
    }
}
