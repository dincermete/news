<?php

namespace App\Services;

class AiSuggestionService
{
    public function __construct(protected OpenAiClient $client) {}

    public function suggestTitle(string $context): string
    {
        return $this->client->chatText([
            [
                'role' => 'system',
                'content' => 'Sen bir SEO başlık yazarısın. Yalnızca tek bir başlık öner. Tırnak veya açıklama ekleme.',
            ],
            [
                'role' => 'user',
                'content' => 'Bağlam: '.$context,
            ],
        ], (int) config('openai.max_tokens.suggestion'));
    }

    public function suggestMetaDescription(string $context): string
    {
        return $this->client->chatText([
            [
                'role' => 'system',
                'content' => 'Sen bir SEO meta açıklama yazarısın. 120-160 karakter arası, tek paragraf, yalnızca açıklama metni döndür.',
            ],
            [
                'role' => 'user',
                'content' => 'Bağlam: '.$context,
            ],
        ], (int) config('openai.max_tokens.suggestion'));
    }
}
