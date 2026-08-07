<?php

namespace Tests\Feature;

use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Models\PromotionalListing;
use App\Models\Site;
use App\Services\ProductPublicUrl;
use App\Services\WooCommerceSiteImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WooCommerceSiteImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_importer_creates_site_and_article_listing_from_csv_row(): void
    {
        Storage::fake('public');

        $csv = $this->tempCsv([
            [
                'Kimlik' => '101',
                'Tür' => 'simple',
                'İsim' => 'Ornek.com Tanıtım Yazısı',
                'Yayımlanmış' => '1',
                'Öne çıkan?' => '0',
                'Kısa açıklama' => 'Kısa özet metin',
                'Açıklama' => '<p>Uzun açıklama</p>',
                'İndirimli satış fiyatı' => '',
                'Normal fiyat' => '1500',
                'Kategoriler' => 'Kategoriler > Haber, Ürünler > Tanıtım Yazısı',
                'Etiketler' => '',
                'Görseller' => '',
                'Tavsiye Ettiklerimiz' => '',
                'Bu ürüne ek olarak' => '',
                'Nitelik 1 değer(ler)i' => '',
                'Meta: site_url' => 'https://www.ornek.com/',
                'Meta: google_index' => '1000',
                'Meta: google_news' => 'Kayıtlı',
                'Meta: gunluk_hit' => '50.000',
                'Meta: link_turu' => 'Dofollow',
                'Meta: moz_da' => '45',
                'Meta: moz_pa' => '30',
                'Meta: link_cikisi' => '2',
                'Meta: site_acilis' => (string) strtotime('2010-01-01'),
                'Meta: teslimat_zamani' => '3 İş Günü',
                'Meta: _alg_wc_cpp_currency' => 'TRY',
                'Meta: site_adsoyad' => 'Ali Veli',
                'Meta: site_telefon' => '0555 111 22 33',
                'Meta: site_email' => 'ali@ornek.com',
                'Meta: site_analytics' => '6629',
                'Meta: rank_math_description' => 'SEO açıklama metni burada',
                'Meta: rank_math_focus_keyword' => 'ornek',
                'Meta: _wp_desired_post_slug' => '',
                'Meta: paket_urunleri' => '',
            ],
        ]);

        $stats = (new WooCommerceSiteImporter(
            publicUrls: app(ProductPublicUrl::class),
            dryRun: false,
            downloadMedia: false,
        ))->import($csv);

        $this->assertSame(1, $stats['sites_upserted']);
        $this->assertSame(1, $stats['listings_upserted']);
        $this->assertSame(1, $stats['owners']);
        $this->assertSame(1, $stats['seo_description']);
        $this->assertSame(1, $stats['seo_keywords']);
        $this->assertSame(1, $stats['analytics_unresolved']);

        $site = Site::query()->where('domain', 'ornek.com')->first();
        $this->assertNotNull($site);
        $this->assertSame(SiteStatus::Active, $site->status);
        $this->assertTrue($site->is_dofollow);
        $this->assertTrue($site->is_news_approved);
        $this->assertSame('Haber', $site->category?->name);
        $this->assertSame('Ali Veli', $site->site_owner_name);
        $this->assertStringContainsString('0555', (string) $site->site_owner_contact);
        $this->assertSame(45.0, (float) $site->da_value);
        $this->assertSame(50000.0, (float) $site->monthly_traffic_value);

        $listing = PromotionalListing::query()
            ->where('site_id', $site->id)
            ->where('type', PromotionalListingType::SiteArticle)
            ->first();

        $this->assertNotNull($listing);
        $this->assertSame('Ornek.com Tanıtım Yazısı', $listing->name);
        $this->assertSame(1500.0, (float) $listing->price);
        $this->assertSame('3 İş Günü', $listing->estimated_delivery);
        $this->assertSame('SEO açıklama metni burada', $listing->meta_description);
        $this->assertSame('ornek', $listing->meta_keywords);
        $this->assertNotEmpty($listing->public_path);
    }

    public function test_dry_run_does_not_persist_records(): void
    {
        $csv = $this->tempCsv([
            [
                'Kimlik' => '202',
                'Tür' => 'simple',
                'İsim' => 'Dryrun.com Tanıtım Yazısı',
                'Yayımlanmış' => '1',
                'Öne çıkan?' => '0',
                'Kısa açıklama' => '',
                'Açıklama' => '',
                'İndirimli satış fiyatı' => '',
                'Normal fiyat' => '100',
                'Kategoriler' => 'Kategoriler > Blog, Ürünler > Tanıtım Yazısı',
                'Etiketler' => '',
                'Görseller' => '',
                'Tavsiye Ettiklerimiz' => '',
                'Bu ürüne ek olarak' => '',
                'Nitelik 1 değer(ler)i' => '',
                'Meta: site_url' => 'https://dryrun.com',
                'Meta: google_index' => '',
                'Meta: google_news' => '',
                'Meta: gunluk_hit' => '',
                'Meta: link_turu' => 'Nofollow',
                'Meta: moz_da' => '',
                'Meta: moz_pa' => '',
                'Meta: link_cikisi' => '',
                'Meta: site_acilis' => '',
                'Meta: teslimat_zamani' => '',
                'Meta: _alg_wc_cpp_currency' => 'TRY',
                'Meta: site_adsoyad' => '',
                'Meta: site_telefon' => '',
                'Meta: site_email' => '',
                'Meta: site_analytics' => '',
                'Meta: rank_math_description' => '',
                'Meta: rank_math_focus_keyword' => '',
                'Meta: _wp_desired_post_slug' => '',
                'Meta: paket_urunleri' => '',
            ],
        ]);

        (new WooCommerceSiteImporter(
            publicUrls: app(ProductPublicUrl::class),
            dryRun: true,
            downloadMedia: false,
        ))->import($csv);

        $this->assertDatabaseMissing(Site::class, ['domain' => 'dryrun.com']);
        $this->assertSame(0, PromotionalListing::query()->count());
    }

    /**
     * @param  list<array<string, string>>  $rows
     */
    protected function tempCsv(array $rows): string
    {
        $headers = array_keys($rows[0]);
        $path = tempnam(sys_get_temp_dir(), 'wcimport').'.csv';
        $handle = fopen($path, 'w');
        $this->assertNotFalse($handle);
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            fputcsv($handle, array_map(fn (string $key): string => $row[$key] ?? '', $headers));
        }
        fclose($handle);

        return $path;
    }
}
