<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MetricSource: string implements HasLabel
{
    case Api = 'api';
    case Manual = 'manual';

    public function getLabel(): string
    {
        return match ($this) {
            self::Api => 'API',
            self::Manual => 'Manuel',
        };
    }
}
