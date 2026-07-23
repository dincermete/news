<?php

namespace Tests\Feature;

use App\Services\AiSuggestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiSuggestionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'openai.api_key' => 'test-key',
            'openai.base_url' => 'https://api.openai.com/v1',
            'openai.model' => 'gpt-4o-mini',
            'openai.max_tokens.suggestion' => 120,
        ]);

        Http::preventStrayRequests();
    }

    public function test_suggest_title_uses_openai_chat_completions(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Örnek SEO Başlığı']],
                ],
            ]),
        ]);

        $title = app(AiSuggestionService::class)->suggestTitle('example.com haber sitesi');

        $this->assertSame('Örnek SEO Başlığı', $title);

        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.openai.com/v1/chat/completions'
            && $request['model'] === 'gpt-4o-mini');
    }

    public function test_suggest_meta_description_returns_trimmed_text(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => "  Meta açıklama metni  \n"]],
                ],
            ]),
        ]);

        $description = app(AiSuggestionService::class)->suggestMetaDescription('paket açıklaması');

        $this->assertSame('Meta açıklama metni', $description);
    }
}
