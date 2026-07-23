<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Models\Page;
use App\Models\Site;
use App\Services\SeoMetaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_for_default_returns_app_name_title(): void
    {
        $meta = app(SeoMetaService::class)->forDefault();

        $this->assertSame(config('app.name'), $meta['title']);
        $this->assertNotEmpty($meta['description']);
        $this->assertNull($meta['og_image']);
    }

    public function test_for_site_uses_domain_and_description(): void
    {
        $site = Site::factory()->create([
            'domain' => 'example-seo.test',
            'description' => 'Kaliteli backlink için örnek site açıklaması.',
            'status' => SiteStatus::Active,
        ]);

        $meta = app(SeoMetaService::class)->forSite($site);

        $this->assertStringContainsString('example-seo.test', $meta['title']);
        $this->assertStringContainsString('Kaliteli backlink', $meta['description']);
        $this->assertStringContainsString('example-seo.test', (string) $meta['og_image']);
    }

    public function test_for_page_prefers_meta_fields(): void
    {
        $page = Page::factory()->create([
            'title' => 'GEO Rehberi',
            'meta_title' => 'GEO | Özel Başlık',
            'meta_description' => 'Özel meta açıklama.',
            'content' => '<p>İçerik gövdesi</p>',
        ]);

        $meta = app(SeoMetaService::class)->forPage($page);

        $this->assertSame('GEO | Özel Başlık', $meta['title']);
        $this->assertSame('Özel meta açıklama.', $meta['description']);
    }

    public function test_home_page_renders_default_meta(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('<title>'.e(config('app.name')).'</title>', false);
        $response->assertSee('name="description"', false);
    }
}
