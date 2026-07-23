<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Models\FakeOrderNotificationName;
use App\Models\FakeOrderNotificationTemplate;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FakeNotificationEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_random_template_and_name_combination(): void
    {
        FakeOrderNotificationTemplate::factory()->create([
            'message_template' => '{isim}, {sehir} şehrinden {urun} satın aldı',
            'is_active' => true,
            'display_interval_seconds' => 45,
        ]);

        FakeOrderNotificationName::factory()->create([
            'name' => 'Ayşe',
            'city' => 'İzmir',
        ]);

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

        $this->assertSame(45, $response->json('display_interval_seconds'));
        $this->assertSame('Ayşe', $response->json('name'));
        $this->assertSame('İzmir', $response->json('city'));
        $this->assertStringContainsString('Ayşe', $response->json('message'));
        $this->assertStringContainsString('İzmir', $response->json('message'));
        $this->assertStringContainsString('ornek-site.com', $response->json('message'));
    }

    public function test_returns_404_when_no_templates_or_names(): void
    {
        $this->getJson(route('api.fake-notification'))
            ->assertNotFound()
            ->assertJson(['message' => null]);
    }

    public function test_ignores_inactive_templates(): void
    {
        FakeOrderNotificationTemplate::factory()->create([
            'is_active' => false,
            'message_template' => '{isim} inactive',
        ]);
        FakeOrderNotificationName::factory()->create();

        $this->getJson(route('api.fake-notification'))
            ->assertNotFound();
    }
}
