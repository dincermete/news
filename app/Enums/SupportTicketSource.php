<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SupportTicketSource: string implements HasLabel
{
    case ChatbotEscalation = 'chatbot_escalation';
    case Manual = 'manual';

    public function getLabel(): string
    {
        return match ($this) {
            self::ChatbotEscalation => 'Chatbot yönlendirme',
            self::Manual => 'Manuel',
        };
    }
}
