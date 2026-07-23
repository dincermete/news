<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\PublishedLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PublishedLink>
 */
class PublishedLinkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $publishedAt = fake()->dateTimeBetween('-3 months', 'now');

        return [
            'order_id' => Order::factory()->status(\App\Enums\OrderStatus::Published),
            'published_url' => fake()->url(),
            'is_live' => true,
            'is_dofollow_verified' => true,
            'last_checked_at' => null,
            'published_at' => $publishedAt,
            'guarantee_until' => (clone $publishedAt)->modify('+6 months'),
        ];
    }

    public function expiredGuarantee(): static
    {
        return $this->state(fn (array $attributes): array => [
            'published_at' => now()->subMonths(7),
            'guarantee_until' => now()->subMonth(),
        ]);
    }

    public function withinGuarantee(): static
    {
        return $this->state(fn (array $attributes): array => [
            'published_at' => now()->subMonth(),
            'guarantee_until' => now()->addMonths(5),
        ]);
    }
}
