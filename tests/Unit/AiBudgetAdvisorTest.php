<?php

namespace Tests\Unit;

use App\Services\AiBudgetAdvisor;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiBudgetAdvisorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'openai.api_key' => 'test-key',
            'openai.base_url' => 'https://api.openai.com/v1',
            'openai.max_tokens.budget_advisor' => 300,
        ]);

        Http::preventStrayRequests();
    }

    public function test_advise_parses_json_category_weights(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => '{"haber":0.4,"teknoloji":0.3,"genel":0.3}',
                    ],
                ]],
            ]),
        ]);

        $weights = app(AiBudgetAdvisor::class)->advise(5000, 'teknoloji görünürlüğü');

        $this->assertSame(0.4, $weights['haber']);
        $this->assertSame(0.3, $weights['teknoloji']);
        $this->assertSame(0.3, $weights['genel']);
    }
}
