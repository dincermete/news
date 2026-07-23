<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContentReviewStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case CustomerReview = 'customer_review';
    case Approved = 'approved';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Taslak',
            self::CustomerReview => 'Müşteri incelemesinde',
            self::Approved => 'Onaylandı',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::CustomerReview => 'warning',
            self::Approved => 'success',
        };
    }
}
