<?php

namespace Database\Factories;

use App\Enums\SiteStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\InstagramAccount>
 */
class InstagramAccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'handle' => '@'.fake()->unique()->userName(),
            'name' => fake()->company(),
            'avatar_url' => null,
            'follower_count' => fake()->numberBetween(5_000, 500_000),
            'status' => SiteStatus::Active,
        ];
    }
}
