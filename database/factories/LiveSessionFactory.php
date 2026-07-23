<?php

namespace Database\Factories;

use App\Models\LiveSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LiveSession>
 */
class LiveSessionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_token' => Str::uuid()->toString(),
            'user_id' => null,
            'current_url' => fake()->url(),
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'last_seen_at' => now(),
        ];
    }

    public function forUser(?User $user = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }

    public function stale(): static
    {
        return $this->state(fn (array $attributes): array => [
            'last_seen_at' => now()->subMinutes(5),
        ]);
    }
}
