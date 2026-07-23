<?php

namespace Database\Factories;

use App\Enums\NotificationAudience;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'audience' => NotificationAudience::All,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
        ];
    }

    public function loggedInOnly(): static
    {
        return $this->state(fn (array $attributes): array => [
            'audience' => NotificationAudience::LoggedInOnly,
        ]);
    }
}
