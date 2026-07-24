<?php

namespace App\Filament\Resources\BacklinkPackages\Pages;

use App\Filament\Resources\BacklinkPackages\BacklinkPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBacklinkPackages extends ListRecords
{
    protected static string $resource = BacklinkPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
