<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_site_catalog_tabs(): void
    {
        Site::factory()->create([
            'status' => SiteStatus::Active,
            'press_release_price' => 150,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Popüler Siteler', false);
        $response->assertSee('Yeni Siteler', false);
        $response->assertSee('Basın Bülteni', false);
        $response->assertSee('Çok Satanlar', false);
    }
}
