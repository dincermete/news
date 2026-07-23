<?php

namespace App\Filament\Resources\OrderGroups\Pages;

use App\Filament\Resources\OrderGroups\OrderGroupResource;
use Filament\Resources\Pages\ListRecords;

class ListOrderGroups extends ListRecords
{
    protected static string $resource = OrderGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
