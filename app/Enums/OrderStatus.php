<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasLabel
{
    case PaymentPending = 'payment_pending';
    case ContentPending = 'content_pending';
    case Review = 'review';
    case InQueue = 'in_queue';
    case Published = 'published';
    case ReportSent = 'report_sent';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::PaymentPending => 'Ödeme bekleniyor',
            self::ContentPending => 'İçerik bekleniyor',
            self::Review => 'İncelemede',
            self::InQueue => 'Yayın kuyruğunda',
            self::Published => 'Yayınlandı',
            self::ReportSent => 'Rapor gönderildi',
            self::Refunded => 'İade edildi',
            self::Cancelled => 'İptal edildi',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PaymentPending => 'warning',
            self::ContentPending => 'info',
            self::Review => 'primary',
            self::InQueue => 'gray',
            self::Published => 'success',
            self::ReportSent => 'success',
            self::Refunded => 'danger',
            self::Cancelled => 'danger',
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PaymentPending => [self::ContentPending, self::Cancelled, self::Refunded],
            self::ContentPending => [self::Review, self::Cancelled, self::Refunded],
            self::Review => [self::InQueue, self::Cancelled, self::Refunded],
            self::InQueue => [self::Published, self::Cancelled, self::Refunded],
            self::Published => [self::ReportSent, self::Refunded],
            self::ReportSent, self::Refunded, self::Cancelled => [],
        };
    }
}
