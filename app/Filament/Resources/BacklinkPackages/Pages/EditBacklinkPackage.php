<?php

namespace App\Filament\Resources\BacklinkPackages\Pages;

use App\Filament\Resources\BacklinkPackages\BacklinkPackageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBacklinkPackage extends EditRecord
{
    protected static string $resource = BacklinkPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
