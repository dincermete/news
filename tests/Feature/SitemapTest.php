<?php

namespace Tests\Feature;

use App\Console\Commands\GenerateSitemap;
use App\Enums\SiteStatus;
use App\Models\Page;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $path = public_path('sitemap.xml');

        if (File::exists($path)) {
            File::delete($path);
        }

        parent::tearDown();
    }

    public function test_generate_sitemap_writes_valid_xml_with_expected_urls(): void
    {
        Page::factory()->create([
            'slug' => 'geo',
            'title' => 'GEO',
            'is_active' => true,
        ]);

        Page::factory()->inactive()->create([
            'slug' => 'pasif-sayfa',
            'title' => 'Pasif',
        ]);

        $activeSites = Site::factory()->count(3)->create([
            'status' => SiteStatus::Active,
        ]);

        Site::factory()->create([
            'status' => SiteStatus::Inactive,
        ]);

        $this->artisan(GenerateSitemap::class)
            ->assertSuccessful();

        $path = public_path('sitemap.xml');
        $this->assertFileExists($path);

        $xml = File::get($path);

        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('<urlset', $xml);
        $this->assertStringContainsString(url('/'), $xml);
        $this->assertStringContainsString(url('/siteler'), $xml);
        $this->assertStringContainsString(route('pages.show', 'geo'), $xml);
        $this->assertStringNotContainsString(route('pages.show', 'pasif-sayfa'), $xml);

        foreach ($activeSites as $site) {
            $this->assertStringContainsString(route('sites.show', $site->domain), $xml);
        }

        $urlCount = substr_count($xml, '<url>');
        // home + siteler + 1 active page + 3 active sites
        $this->assertSame(6, $urlCount);
    }

    public function test_sitemap_route_serves_generated_file(): void
    {
        Page::factory()->create([
            'slug' => 'geo',
            'is_active' => true,
        ]);

        $this->artisan(GenerateSitemap::class)->assertSuccessful();

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false);
    }
}
