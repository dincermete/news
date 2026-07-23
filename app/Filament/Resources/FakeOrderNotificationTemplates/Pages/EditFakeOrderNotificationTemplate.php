<?php

namespace App\Filament\Resources\FakeOrderNotificationTemplates\Pages;

use App\Filament\Resources\FakeOrderNotificationTemplates\FakeOrderNotificationTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFakeOrderNotificationTemplate extends EditRecord
{
    protected static string $resource = FakeOrderNotificationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
