<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FakeNotificationEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_generated_notification_from_product_types(): void
    {
        Site::factory()->create([
            'domain' => 'ornek-site.com',
            'status' => SiteStatus::Active,
        ]);

        $response = $this->getJson(route('api.fake-notification'));

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'display_interval_seconds',
                'name',
                'city',
            ]);

        $this->assertNotEmpty($response->json('message'));
        $this->assertNotEmpty($response->json('name'));
        $this->assertNotEmpty($response->json('city'));
        $this->assertGreaterThanOrEqual(5, $response->json('display_interval_seconds'));
        $this->assertStringContainsString($response->json('name'), $response->json('message'));
    }

    public function test_works_without_active_sites(): void
    {
        $this->getJson(route('api.fake-notification'))
            ->assertOk()
            ->assertJsonStructure(['message', 'display_interval_seconds', 'name', 'city']);
    }
}
