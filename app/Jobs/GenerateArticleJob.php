<?php

namespace App\Jobs;

use App\Enums\ContentReviewStatus;
use App\Models\Order;
use App\Services\ArticleGenerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

class GenerateArticleJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function handle(ArticleGenerationService $generator): void
    {
        $order = $this->order->loadMissing(['articleWordPackage', 'site']);

        $package = $order->articleWordPackage;

        if ($package === null) {
            throw new RuntimeException('Siparişte makale kelime paketi yok.');
        }

        $payload = is_array($order->content_payload) ? $order->content_payload : [];

        $body = $generator->generate($package, [
            'keywords' => $payload['keywords'] ?? null,
            'brief' => $payload['brief'] ?? null,
            'siteUrl' => $payload['siteUrl'] ?? $payload['target_url'] ?? $order->site?->domain,
        ]);

        $order->contentReviews()->create([
            'editor_id' => null,
            'content_body' => $body,
            'status' => ContentReviewStatus::Draft,
        ]);
    }
}
