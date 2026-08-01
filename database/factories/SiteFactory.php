<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\MetricSource;
use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Models\PromotionalListing;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Support\SiteSeoMetrics;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    /**
     * @var \WeakMap<Site, array<string, mixed>>|null
     */
    protected static ?\WeakMap $pendingListingAttributes = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $attributes = [
            'domain' => fake()->unique()->domainName(),
            'site_category_id' => SiteCategory::factory(),
            'description' => fake()->optional()->paragraph(),
            'short_description' => fake()->optional()->sentence(),
            'age' => fake()->numberBetween(1, 25),
            'is_dofollow' => fake()->boolean(80),
            'is_news_approved' => fake()->boolean(30),
            'status' => fake()->randomElement(SiteStatus::cases()),
            'daily_capacity' => fake()->optional()->numberBetween(1, 20),
            'weekly_capacity' => fake()->optional()->numberBetween(5, 100),
            'internal_notes' => fake()->optional()->sentence(),
            'site_owner_name' => fake()->optional()->name(),
            'site_owner_contact' => fake()->optional()->email(),
            'site_owner_payment_info' => fake()->optional()->sentence(),
        ];

        foreach (SiteSeoMetrics::keys() as $metric) {
            $attributes["{$metric}_value"] = fake()->optional(0.7)->randomFloat(2, 0, 100);
            $attributes["{$metric}_source"] = MetricSource::Manual;
            $attributes["{$metric}_updated_at"] = fake()->optional(0.5)->dateTimeBetween('-1 year');
        }

        return $attributes;
    }

    public function configure(): static
    {
        self::$pendingListingAttributes ??= new \WeakMap;

        return $this
            ->afterMaking(function (Site $site): void {
                $pending = [];

                foreach (['price', 'discount_price', 'press_release_price', 'currency'] as $attr) {
                    $attributes = $site->getAttributes();

                    if (! array_key_exists($attr, $attributes)) {
                        continue;
                    }

                    $pending[$attr] = $attributes[$attr];
                    $site->offsetUnset($attr);
                }

                self::$pendingListingAttributes[$site] = $pending;
            })
            ->afterCreating(function (Site $site): void {
                $pending = self::$pendingListingAttributes[$site] ?? [];
                unset(self::$pendingListingAttributes[$site]);

                if ($pending !== []) {
                    $this->createListingsFromLegacyAttributes($site, $pending);

                    return;
                }

                if ($site->promotionalListings()->exists()) {
                    return;
                }

                $price = fake()->randomFloat(2, 10, 500);
                $currency = Currency::Try;
                $status = $site->status instanceof SiteStatus ? $site->status : SiteStatus::Draft;

                PromotionalListing::factory()->article()->for($site)->create([
                    'price' => $price,
                    'discount_price' => null,
                    'currency' => $currency,
                    'status' => $status,
                    'short_description' => $site->short_description,
                    'description' => $site->description,
                ]);

                PromotionalListing::factory()->footerLink()->for($site)->create([
                    'price' => $price,
                    'discount_price' => null,
                    'currency' => $currency,
                    'status' => $status,
                    'short_description' => $site->short_description,
                    'description' => $site->description,
                ]);
            });
    }

    /**
     * @param  array<string, mixed>  $pending
     */
    protected function createListingsFromLegacyAttributes(Site $site, array $pending): void
    {
        $currency = $pending['currency'] ?? Currency::Try;
        if (! $currency instanceof Currency) {
            $currency = Currency::tryFrom((string) $currency) ?? Currency::Try;
        }

        $status = $site->status instanceof SiteStatus ? $site->status : SiteStatus::Draft;

        if (array_key_exists('price', $pending) && $pending['price'] !== null) {
            $price = (float) $pending['price'];
            $discount = array_key_exists('discount_price', $pending) && $pending['discount_price'] !== null
                ? (float) $pending['discount_price']
                : null;

            PromotionalListing::factory()->article()->for($site)->create([
                'price' => $price,
                'discount_price' => $discount,
                'currency' => $currency,
                'status' => $status,
                'short_description' => $site->short_description,
                'description' => $site->description,
            ]);

            $footerPrice = ($discount !== null && $discount < $price) ? $discount : $price;

            PromotionalListing::factory()->footerLink()->for($site)->create([
                'price' => $footerPrice,
                'discount_price' => null,
                'currency' => $currency,
                'status' => $status,
                'short_description' => $site->short_description,
                'description' => $site->description,
            ]);
        }

        if (array_key_exists('press_release_price', $pending) && $pending['press_release_price'] !== null) {
            PromotionalListing::factory()->pressRelease()->for($site)->create([
                'price' => (float) $pending['press_release_price'],
                'discount_price' => null,
                'currency' => $currency,
                'status' => $status,
                'short_description' => $site->short_description,
                'description' => $site->description,
            ]);
        }
    }

    /**
     * Skip auto-created promotional listings (for attaching a specific listing in tests).
     */
    public function withoutListings(): static
    {
        return $this->afterCreating(function (Site $site): void {
            $site->promotionalListings()->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $listing
     */
    public function withArticleListing(array $listing = []): static
    {
        return $this->afterCreating(function (Site $site) use ($listing): void {
            if ($site->articleListing()->exists()) {
                if ($listing !== []) {
                    $site->articleListing()->first()?->update($listing);
                }

                return;
            }

            PromotionalListing::factory()
                ->article()
                ->for($site)
                ->create(array_merge([
                    'status' => $site->status,
                    'currency' => Currency::Try,
                ], $listing));
        });
    }

    /**
     * @param  array<string, mixed>  $listing
     */
    public function withPressReleaseListing(array $listing = []): static
    {
        return $this->afterCreating(function (Site $site) use ($listing): void {
            if ($site->pressReleaseListing()->exists()) {
                if ($listing !== []) {
                    $site->pressReleaseListing()->first()?->update($listing);
                }

                return;
            }

            PromotionalListing::factory()
                ->pressRelease()
                ->for($site)
                ->create(array_merge([
                    'status' => $site->status,
                    'currency' => Currency::Try,
                ], $listing));
        });
    }

    /**
     * @param  array<string, mixed>  $listing
     */
    public function withFooterLinkListing(array $listing = []): static
    {
        return $this->afterCreating(function (Site $site) use ($listing): void {
            if ($site->footerLinkListing()->exists()) {
                if ($listing !== []) {
                    $site->footerLinkListing()->first()?->update($listing);
                }

                return;
            }

            PromotionalListing::factory()
                ->footerLink()
                ->for($site)
                ->create(array_merge([
                    'status' => $site->status,
                    'currency' => Currency::Try,
                ], $listing));
        });
    }

    /**
     * Active site with an active site_article listing (common storefront fixture).
     *
     * @param  array<string, mixed>  $listing
     */
    public function forSale(array $listing = []): static
    {
        return $this
            ->state(['status' => SiteStatus::Active])
            ->withArticleListing(array_merge([
                'status' => SiteStatus::Active,
                'type' => PromotionalListingType::SiteArticle,
            ], $listing));
    }
}
