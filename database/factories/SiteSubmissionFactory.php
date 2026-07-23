<?php

namespace Database\Factories;

use App\Enums\SiteSubmissionStatus;
use App\Models\SiteCategory;
use App\Models\SiteSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSubmission>
 */
class SiteSubmissionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->customer(),
            'url' => 'https://'.fake()->unique()->domainName(),
            'price' => fake()->randomFloat(2, 50, 500),
            'site_category_id' => SiteCategory::factory(),
            'age' => fake()->optional()->numberBetween(1, 20),
            'status' => SiteSubmissionStatus::Pending,
            'admin_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }

    public function approved(?User $reviewer = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SiteSubmissionStatus::Approved,
            'reviewed_by' => $reviewer?->id ?? User::factory()->admin(),
            'reviewed_at' => now(),
            'admin_note' => fake()->optional()->sentence(),
        ]);
    }

    public function rejected(?User $reviewer = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SiteSubmissionStatus::Rejected,
            'reviewed_by' => $reviewer?->id ?? User::factory()->admin(),
            'reviewed_at' => now(),
            'admin_note' => 'Uygun değil: '.fake()->sentence(),
        ]);
    }
}
