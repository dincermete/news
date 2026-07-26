<?php

namespace App\Support;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OrderPresentation
{
    public static function displayTitle(Order $order): string
    {
        return $order->site?->domain
            ?? $order->siteBundle?->name
            ?? $order->instagramAccount?->handle
            ?? $order->seoPackage?->name
            ?? $order->backlinkPackage?->name
            ?? ($order->walletTopupPackage
                ? 'Bakiye · '.self::money($order->walletTopupPackage->amount, $order->currency?->value ?? 'TRY')
                : null)
            ?? $order->product_type?->getLabel()
            ?? 'Ürün #'.$order->id;
    }

    public static function money(float|string|null $amount, ?string $currency = 'TRY'): string
    {
        return number_format((float) ($amount ?? 0), 2, ',', '.').' '.($currency ?: 'TRY');
    }

    /**
     * @return list<array{label: string, value: string, href: ?string, full: bool}>
     */
    public static function payloadRows(Order $order): array
    {
        $payload = is_array($order->content_payload) ? $order->content_payload : [];

        if ($payload === []) {
            return [];
        }

        $rows = [];

        if (filled($payload['target_url'] ?? null)) {
            $rows[] = [
                'label' => 'Hedef URL',
                'value' => (string) $payload['target_url'],
                'href' => (string) $payload['target_url'],
                'full' => true,
            ];
        }

        if (filled($payload['site_address'] ?? null)) {
            $rows[] = [
                'label' => 'Site adresi',
                'value' => (string) $payload['site_address'],
                'href' => Str::startsWith((string) $payload['site_address'], ['http://', 'https://'])
                    ? (string) $payload['site_address']
                    : null,
                'full' => true,
            ];
        }

        if (isset($payload['keywords']) && $payload['keywords'] !== null && $payload['keywords'] !== '' && $payload['keywords'] !== []) {
            $rows[] = [
                'label' => 'Anahtar kelimeler',
                'value' => self::formatKeywords($payload['keywords']),
                'href' => null,
                'full' => true,
            ];
        }

        if (filled($payload['publish_at'] ?? null)) {
            $rows[] = [
                'label' => 'Yayın zamanı',
                'value' => (string) $payload['publish_at'],
                'href' => null,
                'full' => false,
            ];
        }

        if (filled($payload['file_path'] ?? null)) {
            $rows[] = [
                'label' => 'Dosya',
                'value' => basename((string) $payload['file_path']),
                'href' => null,
                'full' => false,
            ];
        }

        if (filled($payload['image_path'] ?? null) || filled($payload['image'] ?? null)) {
            $path = (string) ($payload['image_path'] ?? $payload['image']);
            $rows[] = [
                'label' => 'Görsel',
                'value' => basename($path),
                'href' => null,
                'full' => false,
            ];
        }

        if (filled($payload['brief'] ?? null)) {
            $rows[] = [
                'label' => 'Brief',
                'value' => (string) $payload['brief'],
                'href' => null,
                'full' => true,
            ];
        }

        if (filled($payload['note'] ?? null)) {
            $rows[] = [
                'label' => 'Not',
                'value' => (string) $payload['note'],
                'href' => null,
                'full' => true,
            ];
        }

        $known = ['target_url', 'site_address', 'keywords', 'publish_at', 'file_path', 'image_path', 'image', 'brief', 'note'];

        foreach ($payload as $key => $value) {
            if (in_array($key, $known, true) || $value === null || $value === '' || $value === []) {
                continue;
            }

            $rows[] = [
                'label' => Str::headline((string) $key),
                'value' => is_scalar($value)
                    ? (string) $value
                    : (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'href' => null,
                'full' => true,
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{at: Carbon, label: string, description: ?string, color: string}>
     */
    public static function timeline(Order $order): array
    {
        $events = [];

        if ($order->created_at) {
            $events[] = [
                'at' => $order->created_at,
                'label' => 'Sipariş oluşturuldu',
                'description' => $order->product_type?->getLabel(),
                'color' => 'gray',
            ];
        }

        $payments = self::relevantPayments($order);

        foreach ($payments as $payment) {
            /** @var Payment $payment */
            if ($payment->paid_at) {
                $events[] = [
                    'at' => $payment->paid_at,
                    'label' => 'Ödeme alındı',
                    'description' => trim(($payment->method?->getLabel() ?? '').' · '.self::money($payment->amount, $payment->currency?->value)),
                    'color' => 'success',
                ];
            } elseif ($payment->created_at) {
                $events[] = [
                    'at' => $payment->created_at,
                    'label' => 'Ödeme kaydı',
                    'description' => trim(($payment->status?->getLabel() ?? '').' · '.($payment->method?->getLabel() ?? '')),
                    'color' => 'warning',
                ];
            }
        }

        foreach ($order->contentReviews as $review) {
            if (! $review->created_at) {
                continue;
            }

            $events[] = [
                'at' => $review->created_at,
                'label' => 'İçerik incelemesi v'.$review->version,
                'description' => trim(($review->status?->getLabel() ?? '').($review->editor?->name ? ' · '.$review->editor->name : '')),
                'color' => 'primary',
            ];
        }

        foreach ($order->publishedLinks as $link) {
            if (! $link->published_at) {
                continue;
            }

            $events[] = [
                'at' => $link->published_at,
                'label' => 'Yayınlandı',
                'description' => $link->published_url,
                'color' => 'success',
            ];
        }

        if (in_array($order->status, [OrderStatus::ReportSent, OrderStatus::Refunded, OrderStatus::Cancelled], true) && $order->updated_at) {
            $events[] = [
                'at' => $order->updated_at,
                'label' => $order->status->getLabel(),
                'description' => null,
                'color' => match ($order->status) {
                    OrderStatus::ReportSent => 'success',
                    default => 'danger',
                },
            ];
        }

        return collect($events)
            ->sortByDesc(fn (array $event): int => $event['at']->getTimestamp())
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, Payment>
     */
    public static function relevantPayments(Order $order): Collection
    {
        $orderPayments = $order->relationLoaded('payments')
            ? $order->payments
            : $order->payments()->get();

        $groupPayments = $order->orderGroup?->relationLoaded('payments')
            ? $order->orderGroup->payments
            : ($order->orderGroup?->payments()->get() ?? collect());

        return $orderPayments
            ->concat($groupPayments)
            ->unique('id')
            ->sortByDesc(fn (Payment $payment): int => ($payment->paid_at ?? $payment->created_at)?->getTimestamp() ?? 0)
            ->values();
    }

    protected static function formatKeywords(mixed $keywords): string
    {
        if (is_string($keywords)) {
            return $keywords;
        }

        if (! is_array($keywords)) {
            return (string) json_encode($keywords, JSON_UNESCAPED_UNICODE);
        }

        return collect($keywords)
            ->map(function ($item): string {
                if (is_string($item)) {
                    return $item;
                }

                if (! is_array($item)) {
                    return (string) json_encode($item, JSON_UNESCAPED_UNICODE);
                }

                $word = (string) ($item['word'] ?? '');
                $url = (string) ($item['target_url'] ?? '');

                if ($word !== '' && $url !== '') {
                    return $word.' → '.$url;
                }

                return $word !== '' ? $word : $url;
            })
            ->filter()
            ->implode(', ');
    }
}
