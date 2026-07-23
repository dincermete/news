<?php

namespace Database\Factories;

use App\Enums\ContentReviewStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderContentReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderContentReview>
 */
class OrderContentReviewFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'editor_id' => User::factory()->state(['role' => UserRole::Editor]),
            'content_body' => fake()->paragraphs(3, true),
            'status' => ContentReviewStatus::Draft,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ContentReviewStatus::Approved,
        ]);
    }

    public function customerReview(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ContentReviewStatus::CustomerReview,
        ]);
    }
}
