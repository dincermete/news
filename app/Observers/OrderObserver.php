<?php

namespace App\Observers;

use App\Enums\ContentSource;
use App\Enums\ProductType;
use App\Exceptions\InvalidOrderProductException;
use App\Models\Order;

class OrderObserver
{
    public function creating(Order $order): void
    {
        $this->syncDueDate($order);
        $this->assertProductRequirements($order);
    }

    public function updating(Order $order): void
    {
        if ($order->isDirty('content_source')) {
            $this->syncDueDate($order);
        }

        if ($order->isDirty([
            'product_type',
            'site_id',
            'site_bundle_id',
            'footer_link_duration_option_id',
            'instagram_account_id',
            'instagram_story_price_id',
            'seo_package_id',
            'seo_package_duration_option_id',
            'content_payload',
        ])) {
            $this->assertProductRequirements($order);
        }
    }

    protected function syncDueDate(Order $order): void
    {
        if (! $order->content_source instanceof ContentSource) {
            return;
        }

        $order->due_date = now()
            ->addDays($order->content_source->dueDateDays())
            ->toDateString();
    }

    protected function assertProductRequirements(Order $order): void
    {
        $productType = $order->product_type instanceof ProductType
            ? $order->product_type
            : ProductType::tryFrom((string) $order->product_type) ?? ProductType::SiteArticle;

        match ($productType) {
            ProductType::SiteArticle => $this->requireFilled($order->site_id, 'site_article için site_id zorunludur.'),
            ProductType::PressRelease => $this->requireFilled($order->site_id, 'press_release için site_id zorunludur.'),
            ProductType::Bundle => $this->requireFilled($order->site_bundle_id, 'bundle için site_bundle_id zorunludur.'),
            ProductType::FooterLink => $this->assertFooterLink($order),
            ProductType::Story => $this->assertStoryPayload($order),
            ProductType::SeoPackage => $this->assertSeoPackagePayload($order),
        };
    }

    protected function assertFooterLink(Order $order): void
    {
        $this->requireFilled($order->site_id, 'footer_link için site_id zorunludur.');
        $this->requireFilled(
            $order->footer_link_duration_option_id,
            'footer_link için footer_link_duration_option_id zorunludur.',
        );
    }

    protected function assertStoryPayload(Order $order): void
    {
        $this->requireFilled($order->instagram_account_id, 'story için instagram_account_id zorunludur.');
        $this->requireFilled($order->instagram_story_price_id, 'story için instagram_story_price_id zorunludur.');

        $payload = is_array($order->content_payload) ? $order->content_payload : [];

        if (blank($payload['target_url'] ?? null) && blank($payload['image_path'] ?? null) && blank($payload['image'] ?? null)) {
            throw InvalidOrderProductException::make(
                'story için content_payload içinde target_url veya görsel (image_path) zorunludur.',
            );
        }
    }

    protected function assertSeoPackagePayload(Order $order): void
    {
        $this->requireFilled($order->seo_package_id, 'seo_package için seo_package_id zorunludur.');
        $this->requireFilled($order->seo_package_duration_option_id, 'seo_package için seo_package_duration_option_id zorunludur.');

        $payload = is_array($order->content_payload) ? $order->content_payload : [];

        $this->requireFilled($payload['site_address'] ?? null, 'seo_package için content_payload içinde site_address zorunludur.');

        if (blank($payload['keywords'] ?? null)) {
            throw InvalidOrderProductException::make(
                'seo_package için content_payload içinde en az bir hedef kelime zorunludur.',
            );
        }
    }

    protected function requireFilled(mixed $value, string $message): void
    {
        if (blank($value)) {
            throw InvalidOrderProductException::make($message);
        }
    }
}
