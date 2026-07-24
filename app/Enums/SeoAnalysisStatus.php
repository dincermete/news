<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SeoAnalysisStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case InReview = 'in_review';
    case Completed = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Beklemede',
            self::InReview => 'İnceleniyor',
            self::Completed => 'Tamamlandı',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::InReview => 'info',
            self::Completed => 'success',
        };
    }
}
