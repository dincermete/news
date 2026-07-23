<?php

namespace Database\Factories;

use App\Models\Site;
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
            'user_id' => User::factory(),
            'guest_email' => null,
            'question' => fake()->sentence().'?',
            'answer' => null,
            'answered_by' => null,
            'answered_at' => null,
            'is_public' => true,
        ];
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
