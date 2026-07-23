<?php

namespace App\Services;

use JsonException;
use RuntimeException;

class AiBudgetAdvisor
{
    public function __construct(protected OpenAiClient $client) {}

    /**
     * @return array<string, float>
     */
    public function advise(float $budget, string $goalDescription): array
    {
        $raw = $this->client->chatText([
            [
                'role' => 'system',
                'content' => 'Sen bir medya planlama asistanısın. SADECE geçerli bir JSON nesnesi döndür. Anahtarlar kategori adları, değerler 0-1 arası ağırlıklardır ve toplam yaklaşık 1 olmalıdır. Başka metin yazma.',
            ],
            [
                'role' => 'user',
                'content' => "Bütçe: {$budget} TRY\nHedef: {$goalDescription}\nÖrnek format: {\"haber\":0.4,\"teknoloji\":0.3,\"genel\":0.3}",
            ],
        ], (int) config('openai.max_tokens.budget_advisor'));

        $json = $this->extractJson($raw);

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Bütçe danışmanı geçersiz JSON döndürdü.', previous: $exception);
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('Bütçe danışmanı geçersiz JSON döndürdü.');
        }

        $weights = [];

        foreach ($decoded as $category => $weight) {
            if (! is_string($category) || ! is_numeric($weight)) {
                continue;
            }

            $weights[$category] = round((float) $weight, 4);
        }

        return $weights;
    }

    protected function extractJson(string $raw): string
    {
        $raw = trim($raw);

        if (str_starts_with($raw, '{')) {
            return $raw;
        }

        if (preg_match('/\{.*\}/s', $raw, $matches) === 1) {
            return $matches[0];
        }

        return $raw;
    }
}
