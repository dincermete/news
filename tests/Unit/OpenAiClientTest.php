<?php

namespace Tests\Unit;

use App\Services\OpenAiClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAiClientTest extends TestCase
{
    public function test_gpt5_models_send_max_completion_tokens(): void
    {
        config([
            'openai.api_key' => 'test-key',
            'openai.base_url' => 'https://api.openai.com/v1',
            'openai.model' => 'gpt-5.4-nano-2025-01-01',
        ]);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'ok']],
                ],
            ]),
        ]);

        app(OpenAiClient::class)->chatText([
            ['role' => 'user', 'content' => 'Merhaba'],
        ], 400, 'gpt-5.4-nano-2025-01-01');

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return ($data['max_completion_tokens'] ?? null) === 400
                && ! array_key_exists('max_tokens', $data);
        });
    }

    public function test_legacy_models_still_send_max_tokens(): void
    {
        config([
            'openai.api_key' => 'test-key',
            'openai.base_url' => 'https://api.openai.com/v1',
            'openai.model' => 'gpt-4o-mini',
        ]);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'ok']],
                ],
            ]),
        ]);

        app(OpenAiClient::class)->chatText([
            ['role' => 'user', 'content' => 'Merhaba'],
        ], 120, 'gpt-4o-mini');

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return ($data['max_tokens'] ?? null) === 120
                && ! array_key_exists('max_completion_tokens', $data);
        });
    }
}
