<?php

namespace Tests\Feature;

use App\Console\Commands\GenerateSitemap;
use App\Enums\SiteStatus;
use App\Models\Province;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Support\TurkishProvinces;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProvincePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ProvinceSeeder::class);
    }

    public function test_hub_page_is_removed(): void
    {
        $this->get('/iller')->assertNotFound();
    }

    public function test_province_page_resolves_by_slug(): void
    {
        $this->get(route('provinces.sites', 'nevsehir'))
            ->assertOk()
            ->assertSee('Nevşehir Tanıtım Yazısı Siteleri', false)
            ->assertSee('noindex, follow', false)
            ->assertSee('Sık sorulan sorular', false)
            ->assertSee('FAQPage', false)
            ->assertSee('BreadcrumbList', false);
    }

    public function test_unknown_province_slug_returns_404(): void
    {
        $this->get('/olmayan-il-tanitim-yazisi-siteleri')->assertNotFound();
    }

    public function test_indexable_province_is_index_follow_and_lists_sites(): void
    {
        $province = Province::query()->where('slug', 'istanbul')->firstOrFail();
        $category = SiteCategory::factory()->create(['name' => 'E-ticaret', 'slug' => 'e-ticaret']);

        $sites = Site::factory()
            ->count(3)
            ->create([
                'status' => SiteStatus::Active,
                'site_category_id' => $category->id,
                'da_value' => 40,
            ]);

        foreach ($sites as $site) {
            $site->articleListing()->update(['status' => SiteStatus::Active]);
            $province->sites()->attach($site->id);
        }

        $this->get(route('provinces.sites', 'istanbul'))
            ->assertOk()
            ->assertSee('index, follow', false)
            ->assertSee('3 Site Listelendi', false)
            ->assertSee('E-ticaret', false)
            ->assertSee('ItemList', false)
            ->assertDontSee('noindex, follow', false);
    }

    public function test_thin_province_shows_related_sites_before_publisher_cta(): void
    {
        $nevsehir = Province::query()->where('slug', 'nevsehir')->firstOrFail();
        $istanbul = Province::query()->where('slug', 'istanbul')->firstOrFail();
        $category = SiteCategory::factory()->create(['name' => 'Turizm', 'slug' => 'turizm']);

        $relatedSite = Site::factory()->create([
            'status' => SiteStatus::Active,
            'site_category_id' => $category->id,
            'domain' => 'turizm-ornek.com',
        ]);
        $relatedSite->articleListing()->update(['status' => SiteStatus::Active]);
        $istanbul->sites()->attach($relatedSite->id);

        $html = $this->get(route('provinces.sites', 'nevsehir'))
            ->assertOk()
            ->assertSee('Benzer illerden yayın siteleri', false)
            ->assertSee('turizm-ornek.com', false)
            ->assertSee('Bu ilde sitenizi eklemek ister misiniz?', false)
            ->getContent();

        $relatedPos = strpos($html, 'Benzer illerden yayın siteleri');
        $ctaPos = strpos($html, 'Bu ilde sitenizi eklemek ister misiniz?');

        $this->assertNotFalse($relatedPos);
        $this->assertNotFalse($ctaPos);
        $this->assertLessThan($ctaPos, $relatedPos);
        $this->assertSame(count(TurkishProvinces::all()), Province::query()->count());
        $this->assertSame(0, $nevsehir->sites()->count());
    }

    public function test_sitemap_includes_only_indexable_provinces(): void
    {
        $province = Province::query()->where('slug', 'ankara')->firstOrFail();

        $sites = Site::factory()->count(3)->create(['status' => SiteStatus::Active]);
        foreach ($sites as $site) {
            $site->articleListing()->update(['status' => SiteStatus::Active]);
            $province->sites()->attach($site->id);
        }

        $thin = Province::query()->where('slug', 'bayburt')->firstOrFail();

        $this->artisan(GenerateSitemap::class)->assertSuccessful();

        $xml = File::get(public_path('sitemap.xml'));

        $this->assertStringNotContainsString(url('/iller'), $xml);
        $this->assertStringContainsString($province->url(), $xml);
        $this->assertStringNotContainsString($thin->url(), $xml);

        File::delete(public_path('sitemap.xml'));
    }

    public function test_province_route_does_not_break_catch_all_pages(): void
    {
        $this->get('/nevsehir-tanitim-yazisi-siteleri')->assertOk();
    }
}
