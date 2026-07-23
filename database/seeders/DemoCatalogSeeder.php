<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Enums\Currency;
use App\Enums\SiteStatus;
use App\Enums\SpinPrizeType;
use App\Models\Coupon;
use App\Models\DiscountTier;
use App\Models\FooterLink;
use App\Models\FooterLinkDurationOption;
use App\Models\Label;
use App\Models\Page;
use App\Models\Site;
use App\Models\SiteBundle;
use App\Models\SiteCategory;
use App\Models\SpinWheelPrize;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

/**
 * Demo / local catalog data: 100 active sites + product catalog models.
 *
 * php artisan db:seed --class=DemoCatalogSeeder
 */
class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WalletTopupPackageSeeder::class,
            ArticleWordPackageSeeder::class,
        ]);

        $categories = $this->seedCategories();
        $labels = $this->seedLabels();
        $sites = $this->seedSites($categories, $labels);

        $this->seedSiteBundles($sites);
        $this->seedFooterLinkDurationOptions();
        $this->seedDiscountTiers();
        $this->seedCoupons();
        $this->seedSpinWheelPrizes();
        $this->seedCmsFooterLinks();
        $this->seedCmsPages();

        Cache::forget('catalog.footer_links');
        Cache::forget('catalog.footer_links.v2');
        Cache::forever('catalog.sites.list_version', (string) time());

        $this->command?->info(sprintf(
            'Demo katalog: %d aktif site, %d kategori, %d etiket, %d paket.',
            $sites->count(),
            $categories->count(),
            $labels->count(),
            SiteBundle::query()->count(),
        ));
    }

    /**
     * @return \Illuminate\Support\Collection<int, SiteCategory>
     */
    protected function seedCategories(): \Illuminate\Support\Collection
    {
        $items = [
            ['name' => 'Haber', 'slug' => 'haber'],
            ['name' => 'Blog', 'slug' => 'blog'],
            ['name' => 'Teknoloji', 'slug' => 'teknoloji'],
            ['name' => 'Finans', 'slug' => 'finans'],
            ['name' => 'Sağlık', 'slug' => 'saglik'],
            ['name' => 'Eğitim', 'slug' => 'egitim'],
            ['name' => 'Spor', 'slug' => 'spor'],
            ['name' => 'Yaşam', 'slug' => 'yasam'],
        ];

        return collect($items)->map(
            fn (array $item): SiteCategory => SiteCategory::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'description' => $item['name'].' kategorisindeki yayın siteleri.',
                ],
            ),
        );
    }

    /**
     * @return \Illuminate\Support\Collection<int, Label>
     */
    protected function seedLabels(): \Illuminate\Support\Collection
    {
        $items = [
            ['name' => 'Premium', 'color' => '#d97706'],
            ['name' => 'Hızlı Yayın', 'color' => '#2563eb'],
            ['name' => 'Kalıcı Link', 'color' => '#059669'],
            ['name' => 'Homepage', 'color' => '#7c3aed'],
            ['name' => 'Niche', 'color' => '#db2777'],
        ];

        return collect($items)->map(
            fn (array $item): Label => Label::query()->firstOrCreate(
                ['name' => $item['name']],
                ['color' => $item['color']],
            ),
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, SiteCategory>  $categories
     * @param  \Illuminate\Support\Collection<int, Label>  $labels
     * @return \Illuminate\Database\Eloquent\Collection<int, Site>
     */
    protected function seedSites(
        \Illuminate\Support\Collection $categories,
        \Illuminate\Support\Collection $labels,
    ): \Illuminate\Database\Eloquent\Collection {
        $existingActive = Site::query()->where('status', SiteStatus::Active)->count();
        $needed = max(0, 100 - $existingActive);

        if ($needed === 0) {
            $this->command?->warn('Zaten en az 100 aktif site var; yeni site eklenmedi.');

            return Site::query()
                ->where('status', SiteStatus::Active)
                ->with(['category', 'labels'])
                ->limit(100)
                ->get();
        }

        $created = Site::factory()
            ->count($needed)
            ->state(fn (): array => [
                'site_category_id' => $categories->random()->id,
                'status' => SiteStatus::Active,
                'currency' => fake()->randomElement([Currency::Try, Currency::Usd]),
                'price' => fake()->randomFloat(2, 25, 800),
                'discount_price' => fake()->boolean(35)
                    ? fake()->randomFloat(2, 15, 400)
                    : null,
                'da_value' => fake()->randomFloat(2, 5, 90),
                'pa_value' => fake()->randomFloat(2, 5, 85),
                'is_dofollow' => fake()->boolean(75),
                'is_news_approved' => fake()->boolean(35),
                'description' => fake()->paragraph(),
            ])
            ->create();

        foreach ($created as $site) {
            $site->labels()->sync(
                $labels->random(fake()->numberBetween(0, 3))->pluck('id')->all(),
            );
        }

        return Site::query()
            ->where('status', SiteStatus::Active)
            ->with(['category', 'labels'])
            ->latest('id')
            ->limit(100)
            ->get();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Site>  $sites
     */
    protected function seedSiteBundles(\Illuminate\Database\Eloquent\Collection $sites): void
    {
        $bundles = [
            ['name' => 'Starter Paket', 'price' => 499],
            ['name' => 'Growth Paket', 'price' => 1299],
            ['name' => 'Authority Paket', 'price' => 2499],
            ['name' => 'News Odaklı Paket', 'price' => 1899],
            ['name' => 'Niche Bundle', 'price' => 899],
        ];

        foreach ($bundles as $index => $data) {
            $bundle = SiteBundle::query()->updateOrCreate(
                ['name' => $data['name']],
                [
                    'description' => $data['name'].' — demo paket açıklaması.',
                    'price' => $data['price'],
                    'currency' => Currency::Try,
                    'status' => SiteStatus::Active,
                ],
            );

            $bundle->sites()->sync(
                $sites->random(min(8, $sites->count()))->pluck('id')->all(),
            );
        }
    }

    protected function seedFooterLinkDurationOptions(): void
    {
        $options = [
            ['name' => '1 Aylık', 'months' => 1, 'price_multiplier' => 1.0],
            ['name' => '3 Aylık', 'months' => 3, 'price_multiplier' => 2.5],
            ['name' => '6 Aylık', 'months' => 6, 'price_multiplier' => 4.5],
            ['name' => '12 Aylık', 'months' => 12, 'price_multiplier' => 8.0],
            ['name' => 'Kalıcı (Flat)', 'months' => 12, 'price_multiplier' => null, 'flat_price' => 1500],
        ];

        foreach ($options as $option) {
            FooterLinkDurationOption::query()->updateOrCreate(
                ['name' => $option['name']],
                [
                    'months' => $option['months'],
                    'price_multiplier' => $option['price_multiplier'] ?? null,
                    'flat_price' => $option['flat_price'] ?? null,
                    'is_active' => true,
                ],
            );
        }
    }

    protected function seedDiscountTiers(): void
    {
        $tiers = [
            ['min_cart_amount' => 500, 'discount_percentage' => 5, 'sort_order' => 1],
            ['min_cart_amount' => 1000, 'discount_percentage' => 10, 'sort_order' => 2],
            ['min_cart_amount' => 2500, 'discount_percentage' => 15, 'sort_order' => 3],
            ['min_cart_amount' => 5000, 'discount_percentage' => 20, 'sort_order' => 4],
        ];

        foreach ($tiers as $tier) {
            DiscountTier::query()->updateOrCreate(
                ['min_cart_amount' => $tier['min_cart_amount']],
                [
                    'discount_percentage' => $tier['discount_percentage'],
                    'sort_order' => $tier['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }

    protected function seedCoupons(): void
    {
        Coupon::query()->updateOrCreate(
            ['code' => 'DEMO10'],
            [
                'type' => CouponType::Percentage,
                'value' => 10,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addYear(),
                'usage_limit' => 1000,
                'used_count' => 0,
                'min_cart_amount' => 100,
                'is_active' => true,
            ],
        );

        Coupon::query()->updateOrCreate(
            ['code' => 'HOSGELDIN50'],
            [
                'type' => CouponType::FixedAmount,
                'value' => 50,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addMonths(6),
                'usage_limit' => 500,
                'used_count' => 0,
                'min_cart_amount' => 200,
                'is_active' => true,
            ],
        );

        Coupon::factory()->count(3)->create([
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addMonths(3),
        ]);
    }

    protected function seedSpinWheelPrizes(): void
    {
        $prizes = [
            ['name' => '10 TL Bakiye', 'type' => SpinPrizeType::Balance, 'value' => 10, 'probability_weight' => 20],
            ['name' => '25 TL Bakiye', 'type' => SpinPrizeType::Balance, 'value' => 25, 'probability_weight' => 10],
            ['name' => '50 TL Bakiye', 'type' => SpinPrizeType::Balance, 'value' => 50, 'probability_weight' => 5],
            ['name' => '100 TL Bakiye', 'type' => SpinPrizeType::Balance, 'value' => 100, 'probability_weight' => 2],
            ['name' => 'Boş', 'type' => SpinPrizeType::None, 'value' => null, 'probability_weight' => 40],
        ];

        foreach ($prizes as $prize) {
            SpinWheelPrize::query()->updateOrCreate(
                ['name' => $prize['name']],
                [
                    'type' => $prize['type'],
                    'value' => $prize['value'],
                    'probability_weight' => $prize['probability_weight'],
                    'stock' => null,
                    'is_active' => true,
                ],
            );
        }
    }

    protected function seedCmsFooterLinks(): void
    {
        $links = [
            ['label' => 'Hakkımızda', 'url' => '/hakkimizda', 'group' => 'Kurumsal', 'sort_order' => 1],
            ['label' => 'İletişim', 'url' => '/iletisim', 'group' => 'Kurumsal', 'sort_order' => 2],
            ['label' => 'GEO', 'url' => '/geo', 'group' => 'Hizmetler', 'sort_order' => 1],
            ['label' => 'Yapay Zeka Görünürlük', 'url' => '/yapay-zeka-gorunurluk', 'group' => 'Hizmetler', 'sort_order' => 2],
            ['label' => 'Backlink Paketleri', 'url' => '/backlink-paketleri', 'group' => 'Hizmetler', 'sort_order' => 3],
            ['label' => 'Gizlilik', 'url' => '/gizlilik', 'group' => 'Yasal', 'sort_order' => 1],
            ['label' => 'Kullanım Koşulları', 'url' => '/kullanim-kosullari', 'group' => 'Yasal', 'sort_order' => 2],
        ];

        foreach ($links as $link) {
            FooterLink::query()->updateOrCreate(
                ['url' => $link['url']],
                [
                    'label' => $link['label'],
                    'group' => $link['group'],
                    'sort_order' => $link['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }

    protected function seedCmsPages(): void
    {
        $pages = [
            [
                'slug' => 'geo',
                'title' => 'GEO',
                'meta_title' => 'GEO Hizmetleri',
                'meta_description' => 'Generative Engine Optimization ile AI arama görünürlüğü.',
            ],
            [
                'slug' => 'yapay-zeka-gorunurluk',
                'title' => 'Yapay Zeka Görünürlük',
                'meta_title' => 'Yapay Zeka Görünürlük',
                'meta_description' => 'ChatGPT, Perplexity ve AI Overviews görünürlük paketleri.',
            ],
            [
                'slug' => 'backlink-paketleri',
                'title' => 'Backlink Paketleri',
                'meta_title' => 'Backlink Paketleri',
                'meta_description' => 'Hazır backlink ve site paketleri.',
            ],
            [
                'slug' => 'hakkimizda',
                'title' => 'Hakkımızda',
                'meta_title' => 'Hakkımızda',
                'meta_description' => 'Şirket hakkında kısa bilgi.',
            ],
        ];

        foreach ($pages as $page) {
            Page::query()->updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'meta_title' => $page['meta_title'],
                    'meta_description' => $page['meta_description'],
                    'content' => '<p>'.e($page['title']).' sayfası demo içeriğidir. Faz 11f ile düzenlenecek.</p>',
                    'is_active' => true,
                ],
            );
        }
    }
}
