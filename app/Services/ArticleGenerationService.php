<?php

namespace App\Services;

use App\Models\ArticleWordPackage;

class ArticleGenerationService
{
    public function __construct(protected OpenAiClient $client) {}

    /**
     * @param  array{keywords?: string|list<string>|null, brief?: string|null, siteUrl?: string|null, target_url?: string|null}  $params
     */
    public function generate(ArticleWordPackage $package, array $params): string
    {
        $keywords = $params['keywords'] ?? '';
        if (is_array($keywords)) {
            $keywords = implode(', ', $keywords);
        }

        $brief = (string) ($params['brief'] ?? '');
        $siteUrl = (string) ($params['siteUrl'] ?? $params['target_url'] ?? '');
        $wordCount = (int) $package->word_count;

        $userPrompt = implode("\n", array_filter([
            "Hedef kelime sayısı: {$wordCount}",
            $keywords !== '' ? "Anahtar kelimeler: {$keywords}" : null,
            $brief !== '' ? "Brief: {$brief}" : null,
            $siteUrl !== '' ? "Hedef URL / site: {$siteUrl}" : null,
            'Türkçe, SEO uyumlu, özgün bir makale yaz. Yalnızca makale metnini döndür.',
        ]));

        return $this->client->chatText([
            [
                'role' => 'system',
                'content' => 'Sen profesyonel bir içerik yazarısın. Verilen brief ve anahtar kelimelere uygun makale üretirsin.',
            ],
            [
                'role' => 'user',
                'content' => $userPrompt,
            ],
        ], (int) config('openai.max_tokens.article'), (string) config('openai.article_model'));
    }
}
