<?php

namespace App\Filament\Resources\SeoPackages\Pages;

use App\Filament\Resources\SeoPackages\SeoPackageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSeoPackage extends EditRecord
{
    protected static string $resource = SeoPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
