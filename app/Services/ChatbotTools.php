<?php

namespace App\Services;

use App\Models\FaqEntry;
use App\Models\Site;
use App\Models\SiteCategory;

class ChatbotTools
{
    public function __construct(protected BudgetPackageSuggester $suggester) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function definitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_sites',
                    'description' => 'Verilen bütçe ve isteğe bağlı kategoriye göre aktif site önerileri döndürür. Fiyat uydurma; yalnızca gerçek envanter.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'budget' => [
                                'type' => 'number',
                                'description' => 'Maksimum bütçe (TRY)',
                            ],
                            'count' => [
                                'type' => 'integer',
                                'description' => 'Döndürülecek maksimum site sayısı',
                            ],
                            'category' => [
                                'type' => 'string',
                                'description' => 'Opsiyonel kategori adı (ör. haber, teknoloji)',
                            ],
                        ],
                        'required' => ['budget', 'count'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_faq_answer',
                    'description' => 'SSS havuzundan konu başlığına en yakın cevabı getirir (platform politikaları, DA/PA nedir vb.).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'topic' => [
                                'type' => 'string',
                                'description' => 'Aranan konu veya soru özeti',
                            ],
                        ],
                        'required' => ['topic'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function call(string $name, array $arguments): mixed
    {
        return match ($name) {
            'search_sites' => $this->searchSites(
                budget: (float) ($arguments['budget'] ?? 0),
                count: (int) ($arguments['count'] ?? 5),
                category: isset($arguments['category']) ? (string) $arguments['category'] : null,
            ),
            'get_faq_answer' => $this->getFaqAnswer((string) ($arguments['topic'] ?? '')),
            default => ['error' => 'Bilinmeyen araç: '.$name],
        };
    }

    /**
     * @return list<array{domain: string, price: float, da: float|null, is_news_approved: bool}>
     */
    public function searchSites(float $budget, int $count, ?string $category = null): array
    {
        $categoryId = null;

        if (filled($category)) {
            $categoryId = SiteCategory::query()
                ->where('name', 'like', '%'.$category.'%')
                ->value('id');
        }

        $result = $this->suggester->suggest($budget, $categoryId);
        $sites = collect($result['sites'])->take(max(1, $count));

        return $sites->map(fn (Site $site): array => [
            'domain' => $site->domain,
            'price' => (float) $site->price,
            'da' => $site->da_value !== null ? (float) $site->da_value : null,
            'is_news_approved' => (bool) ($site->is_news_approved ?? false),
        ])->values()->all();
    }

    public function getFaqAnswer(string $topic): ?string
    {
        $topic = trim($topic);

        if ($topic === '') {
            return null;
        }

        $entry = FaqEntry::query()
            ->active()
            ->where(function ($query) use ($topic): void {
                $query->where('question_topic', 'like', '%'.$topic.'%')
                    ->orWhere('answer', 'like', '%'.$topic.'%')
                    ->orWhere('category', 'like', '%'.$topic.'%');
            })
            ->orderBy('id')
            ->first();

        return $entry?->answer;
    }
}
