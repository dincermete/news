<?php

namespace Database\Factories;

use App\Enums\ContentMode;
use App\Enums\Currency;
use App\Enums\ProductType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'product_type' => ProductType::SiteArticle,
            'site_id' => Site::factory(),
            'site_bundle_id' => null,
            'footer_link_duration_option_id' => null,
            'article_word_package_id' => null,
            'content_mode' => ContentMode::FileUpload,
            'content_payload' => ['target_url' => fake()->url()],
            'price' => fake()->randomFloat(2, 50, 300),
            'currency' => Currency::Try,
        ];
    }

    public function bundle(): static
    {
        return $this->state(fn (array $attributes): array => [
            'product_type' => ProductType::Bundle,
            'site_id' => null,
            'site_bundle_id' => \App\Models\SiteBundle::factory(),
        ]);
    }

    public function story(): static
    {
        return $this->state(fn (array $attributes): array => [
            'product_type' => ProductType::Story,
            'site_id' => null,
            'content_payload' => [
                'target_url' => fake()->url(),
                'image_path' => 'stories/sample.jpg',
            ],
        ]);
    }
}
