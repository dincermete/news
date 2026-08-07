<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Models\Site;
use App\Models\SiteCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HeaderRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $category = SiteCategory::factory()->create(['name' => 'Haber', 'slug' => 'haber']);
        Site::factory()->create(['site_category_id' => $category->id, 'status' => SiteStatus::Active]);
    }

    public function test_home_page_renders_header_without_errors(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Popüler Kategoriler', false);
        $response->assertSee('Siteni Ücretsiz Ekle', false);
        $response->assertSee('SEO ROI Hesaplayıcı', false);
    }

    /**
     * @return list<string>
     */
    public static function otherHeaderPages(): array
    {
        return [
            ['/siteler'],
            ['/tanitim-paketleri'],
            ['/geo'],
            ['/seo-paketleri'],
            ['/backlink-paketleri'],
            ['/hakkimizda'],
            ['/iletisim'],
            ['/blog'],
            ['/sepet'],
            ['/araclar'],
        ];
    }

    #[DataProvider('otherHeaderPages')]
    public function test_other_storefront_pages_render_header_without_errors(string $path): void
    {
        $response = $this->get($path);

        $response->assertStatus(200);
    }

    public function test_site_category_page_renders_header_with_active_category_highlighted(): void
    {
        $response = $this->get('/siteler/kategori/haber');

        $response->assertStatus(200);
    }
}
