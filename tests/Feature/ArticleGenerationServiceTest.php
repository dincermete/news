<?php

namespace Tests\Feature;

use App\Enums\ContentMode;
use App\Enums\ContentReviewStatus;
use App\Jobs\GenerateArticleJob;
use App\Models\ArticleWordPackage;
use App\Models\Order;
use App\Models\OrderContentReview;
use App\Services\ArticleGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ArticleGenerationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'openai.api_key' => 'test-key',
            'openai.base_url' => 'https://api.openai.com/v1',
            'openai.article_model' => 'gpt-4o-mini',
            'openai.max_tokens.article' => 4000,
        ]);

        Http::preventStrayRequests();
    }

    public function test_generate_returns_article_body_from_openai(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Üretilmiş makale metni']],
                ],
            ]),
        ]);

        $package = ArticleWordPackage::factory()->create(['word_count' => 300]);

        $body = app(ArticleGenerationService::class)->generate($package, [
            'keywords' => 'seo, backlink',
            'brief' => 'Kısa brief',
            'siteUrl' => 'https://example.com',
        ]);

        $this->assertSame('Üretilmiş makale metni', $body);
    }

    public function test_generate_article_job_creates_draft_content_review(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'AI makale gövdesi']],
                ],
            ]),
        ]);

        $package = ArticleWordPackage::factory()->create(['word_count' => 200]);
        $order = Order::factory()->create([
            'content_mode' => ContentMode::AiArticle,
            'article_word_package_id' => $package->id,
            'content_payload' => [
                'keywords' => 'test',
                'brief' => 'brief',
            ],
        ]);

        (new GenerateArticleJob($order))->handle(app(ArticleGenerationService::class));

        $this->assertDatabaseHas(OrderContentReview::class, [
            'order_id' => $order->id,
            'content_body' => 'AI makale gövdesi',
            'status' => ContentReviewStatus::Draft->value,
            'version' => 1,
        ]);
    }

    public function test_generate_article_job_can_be_dispatched(): void
    {
        Queue::fake();

        $order = Order::factory()->create([
            'content_mode' => ContentMode::AiArticle,
            'article_word_package_id' => ArticleWordPackage::factory(),
        ]);

        GenerateArticleJob::dispatch($order);

        Queue::assertPushed(GenerateArticleJob::class);
    }
}
