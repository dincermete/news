<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContentSource: string implements HasLabel
{
    case CustomerProvided = 'customer_provided';
    case AgencyWritten = 'agency_written';

    public function getLabel(): string
    {
        return match ($this) {
            self::CustomerProvided => 'Müşteri sağladı',
            self::AgencyWritten => 'Ajans yazacak',
        };
    }

    public function dueDateDays(): int
    {
        return match ($this) {
            self::CustomerProvided => 2,
            self::AgencyWritten => 7,
        };
    }
}
