<?php

namespace App\Filament\Resources\SupportTickets\Pages;

use App\Filament\Resources\SupportTickets\Actions\SupportTicketActions;
use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Models\SupportTicket;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class ViewSupportTicket extends ViewRecord
{
    protected static string $resource = SupportTicketResource::class;

    protected function resolveRecord(int | string $key): Model
    {
        /** @var SupportTicket $record */
        $record = parent::resolveRecord($key);

        $record->load([
            'user',
            'assignee',
            'messages.user',
            'notes.admin',
            'chatbotConversation',
        ]);

        return $record;
    }

    public function getTitle(): string | Htmlable
    {
        return 'Talep #'.$this->getRecord()->getKey();
    }

    public function getHeading(): string | Htmlable | null
    {
        return 'Talep #'.$this->getRecord()->getKey();
    }

    protected function getHeaderActions(): array
    {
        return SupportTicketActions::make();
    }
}
