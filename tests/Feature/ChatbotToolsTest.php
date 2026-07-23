<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Models\FaqEntry;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Services\ChatbotTools;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_sites_returns_real_inventory_rows(): void
    {
        $category = SiteCategory::factory()->create(['name' => 'Haber']);

        Site::factory()->create([
            'domain' => 'news-a.test',
            'price' => 100,
            'status' => SiteStatus::Active,
            'site_category_id' => $category->id,
            'da_value' => 40,
            'is_news_approved' => true,
        ]);
        Site::factory()->create([
            'domain' => 'news-b.test',
            'price' => 150,
            'status' => SiteStatus::Active,
            'site_category_id' => $category->id,
            'da_value' => 55,
            'is_news_approved' => false,
        ]);
        Site::factory()->create([
            'domain' => 'inactive.test',
            'price' => 50,
            'status' => SiteStatus::Draft,
            'site_category_id' => $category->id,
        ]);

        $results = app(ChatbotTools::class)->searchSites(200, 5, 'haber');

        $this->assertNotEmpty($results);
        $this->assertSame('news-a.test', $results[0]['domain']);
        $this->assertArrayHasKey('price', $results[0]);
        $this->assertArrayHasKey('da', $results[0]);
        $this->assertArrayHasKey('is_news_approved', $results[0]);
        $this->assertFalse(collect($results)->contains(fn (array $row): bool => $row['domain'] === 'inactive.test'));
    }

    public function test_get_faq_answer_matches_topic(): void
    {
        FaqEntry::factory()->create([
            'question_topic' => 'DA PA nedir',
            'answer' => 'Domain Authority ve Page Authority skorlarıdır.',
            'is_active' => true,
        ]);

        $answer = app(ChatbotTools::class)->getFaqAnswer('DA PA');

        $this->assertSame('Domain Authority ve Page Authority skorlarıdır.', $answer);
    }
}
