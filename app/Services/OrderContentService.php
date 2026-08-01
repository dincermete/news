<?php

namespace App\Services;

use App\Enums\ContentMode;
use App\Enums\ProductType;
use App\Models\ArticleWordPackage;
use App\Models\Order;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class OrderContentService
{
    /**
     * @param  array{
     *     content_mode?: string|null,
     *     target_url?: string|null,
     *     keywords?: string|array|null,
     *     brief?: string|null,
     *     article_word_package_id?: int|null,
     *     file?: UploadedFile|null,
     *     image?: UploadedFile|null,
     *     publish_at?: string|null,
     *     note?: string|null,
     *     site_address?: string|null
     * }  $data
     */
    public function update(Order $order, array $data): Order
    {
        if (! $order->canEditContent()) {
            throw ValidationException::withMessages([
                'content' => 'Bu sipariş için içerik artık düzenlenemez.',
            ]);
        }

        return match ($order->product_type) {
            ProductType::FooterLink => $this->updateFooterLinkContent($order, $data),
            ProductType::SeoPackage, ProductType::BacklinkPackage => $this->updateKeywordTargetingContent($order, $data),
            ProductType::Balance => $order,
            default => $this->updateArticleLikeContent($order, $data),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function updateArticleLikeContent(Order $order, array $data): Order
    {
        $mode = ContentMode::from($data['content_mode']);
        $payload = is_array($order->content_payload) ? $order->content_payload : [];

        $payload['target_url'] = filled($data['target_url'] ?? null)
            ? (string) $data['target_url']
            : ($payload['target_url'] ?? null);
        $payload['publish_at'] = filled($data['publish_at'] ?? null)
            ? (string) $data['publish_at']
            : ($payload['publish_at'] ?? null);
        $payload['note'] = filled($data['note'] ?? null)
            ? (string) $data['note']
            : ($payload['note'] ?? null);

        if (isset($data['image']) && $data['image'] !== null) {
            $payload['image_path'] = $data['image']->store('order-content/'.$order->id, 'local');
        }

        $packageId = $order->article_word_package_id;

        if ($mode === ContentMode::FileUpload) {
            if (isset($data['file']) && $data['file'] !== null) {
                $payload['file_path'] = $data['file']->store('order-content/'.$order->id, 'local');
            }

            unset($payload['keywords'], $payload['brief']);
            $packageId = null;
        }

        if ($mode === ContentMode::AiArticle) {
            $packageId = (int) ($data['article_word_package_id'] ?? $order->article_word_package_id ?? 0);
            $package = ArticleWordPackage::query()
                ->whereKey($packageId)
                ->where('is_active', true)
                ->first();

            if ($package === null) {
                throw ValidationException::withMessages([
                    'article_word_package_id' => 'Geçerli bir makale paketi seçin.',
                ]);
            }

            $payload['keywords'] = filled($data['keywords'] ?? null)
                ? (string) $data['keywords']
                : ($payload['keywords'] ?? null);
            $payload['brief'] = filled($data['brief'] ?? null)
                ? (string) $data['brief']
                : ($payload['brief'] ?? null);

            unset($payload['file_path']);
            $packageId = $package->id;
        }

        $order->forceFill([
            'content_mode' => $mode,
            'content_payload' => $payload,
            'article_word_package_id' => $packageId,
        ])->save();

        return $order->fresh([
            'site',
            'siteBundle',
            'articleWordPackage',
            'footerLinkDurationOption',
            'seoPackage',
            'seoPackageDurationOption',
            'backlinkPackage',
        ]) ?? $order;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function updateFooterLinkContent(Order $order, array $data): Order
    {
        $payload = is_array($order->content_payload) ? $order->content_payload : [];

        $payload['target_url'] = filled($data['target_url'] ?? null)
            ? (string) $data['target_url']
            : ($payload['target_url'] ?? null);
        $payload['keywords'] = filled($data['keywords'] ?? null)
            ? (string) $data['keywords']
            : ($payload['keywords'] ?? null);
        $payload['note'] = filled($data['note'] ?? null)
            ? (string) $data['note']
            : ($payload['note'] ?? null);

        $order->forceFill([
            'content_mode' => ContentMode::None,
            'content_payload' => $payload,
        ])->save();

        return $order->fresh(['site', 'footerLinkDurationOption']) ?? $order;
    }

    /**
     * @param  array{
     *     site_address?: string|null,
     *     keywords?: array<int, array{word: string, target_url?: string|null}>|null,
     *     note?: string|null
     * }  $data
     */
    protected function updateKeywordTargetingContent(Order $order, array $data): Order
    {
        $payload = is_array($order->content_payload) ? $order->content_payload : [];

        $payload['site_address'] = filled($data['site_address'] ?? null)
            ? (string) $data['site_address']
            : ($payload['site_address'] ?? null);
        $payload['keywords'] = ! empty($data['keywords'])
            ? array_values($data['keywords'])
            : ($payload['keywords'] ?? []);
        $payload['note'] = filled($data['note'] ?? null)
            ? (string) $data['note']
            : ($payload['note'] ?? null);

        $order->forceFill([
            'content_mode' => ContentMode::None,
            'content_payload' => $payload,
        ])->save();

        $relations = $order->product_type === ProductType::BacklinkPackage
            ? ['backlinkPackage']
            : ['seoPackage', 'seoPackageDurationOption'];

        return $order->fresh($relations) ?? $order;
    }
}
