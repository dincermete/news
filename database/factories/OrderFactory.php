<?php

namespace Database\Factories;

use App\Enums\ContentSource;
use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'site_id' => Site::factory(),
            'site_package_id' => null,
            'status' => OrderStatus::PaymentPending,
            'content_source' => fake()->randomElement(ContentSource::cases()),
            'price' => fake()->randomFloat(2, 50, 500),
            'currency' => fake()->randomElement(Currency::cases()),
            'assigned_editor_id' => null,
            'product_type' => \App\Enums\ProductType::SiteArticle,
        ];
    }

    public function withEditor(?User $editor = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'assigned_editor_id' => $editor?->id ?? User::factory()->editor(),
        ]);
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => $status,
        ]);
    }

    public function customerProvided(): static
    {
        return $this->state(fn (array $attributes): array => [
            'content_source' => ContentSource::CustomerProvided,
        ]);
    }

    public function agencyWritten(): static
    {
        return $this->state(fn (array $attributes): array => [
            'content_source' => ContentSource::AgencyWritten,
        ]);
    }
}
