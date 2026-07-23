<?php

namespace App\Filament\Resources\FakeOrderNotificationNames\Pages;

use App\Filament\Resources\FakeOrderNotificationNames\FakeOrderNotificationNameResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFakeOrderNotificationName extends EditRecord
{
    protected static string $resource = FakeOrderNotificationNameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
