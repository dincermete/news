<?php

namespace App\Filament\Resources\PanelUsers\Pages;

use App\Filament\Resources\PanelUsers\PanelUserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPanelUsers extends ListRecords
{
    protected static string $resource = PanelUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
