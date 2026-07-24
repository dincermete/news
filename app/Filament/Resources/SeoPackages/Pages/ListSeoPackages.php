<?php

namespace App\Filament\Resources\SeoPackages\Pages;

use App\Filament\Resources\SeoPackages\SeoPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeoPackages extends ListRecords
{
    protected static string $resource = SeoPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
