<?php

namespace App\Filament\Resources\FakeOrderNotificationTemplates\Pages;

use App\Filament\Resources\FakeOrderNotificationTemplates\FakeOrderNotificationTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFakeOrderNotificationTemplates extends ListRecords
{
    protected static string $resource = FakeOrderNotificationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
