<?php

namespace App\Filament\Resources\FakeOrderNotificationNames\Pages;

use App\Filament\Resources\FakeOrderNotificationNames\FakeOrderNotificationNameResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFakeOrderNotificationNames extends ListRecords
{
    protected static string $resource = FakeOrderNotificationNameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
