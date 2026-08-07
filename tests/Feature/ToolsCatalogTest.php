<?php

namespace Tests\Feature;

use App\Support\Tools;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ToolsCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_tools_index_lists_every_tool(): void
    {
        $response = $this->get(route('tools.index'));

        $response->assertOk();

        foreach (Tools::all() as $tool) {
            $response->assertSee($tool['name']);
        }
    }

    public function test_each_tool_show_page_renders(): void
    {
        foreach (Tools::all() as $tool) {
            $this->get(route('tools.show', $tool['slug']))
                ->assertOk()
                ->assertSee($tool['name']);
        }
    }

    public function test_unknown_tool_slug_is_404(): void
    {
        $this->get(route('tools.show', 'olmayan-arac'))->assertNotFound();
    }

    public function test_ai_crawler_check_reports_blocked_bot(): void
    {
        Http::fake([
            'https://example-blocked.com/robots.txt' => Http::response(
                "User-agent: GPTBot\nDisallow: /\n\nUser-agent: *\nDisallow:\n",
                200,
            ),
        ]);

        $response = $this->postJson(route('tools.ai-crawler-check'), [
            'domain' => 'example-blocked.com',
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['label' => 'OpenAI · GPTBot', 'status' => 'blocked']);
    }

    public function test_ai_crawler_check_defaults_to_allowed_when_robots_missing(): void
    {
        Http::fake([
            'https://example-missing.com/robots.txt' => Http::response('', 404),
            'http://example-missing.com/robots.txt' => Http::response('', 404),
        ]);

        $response = $this->postJson(route('tools.ai-crawler-check'), [
            'domain' => 'example-missing.com',
        ]);

        $response->assertOk();
        $response->assertJson(['robots_found' => false]);
    }

    public function test_ai_crawler_check_rejects_invalid_domain(): void
    {
        $this->postJson(route('tools.ai-crawler-check'), [
            'domain' => 'not a domain!!',
        ])->assertStatus(422);
    }
}
