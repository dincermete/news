<?php

namespace App\Filament\Resources\SiteBundles\Pages;

use App\Filament\Resources\SiteBundles\SiteBundleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSiteBundles extends ListRecords
{
    protected static string $resource = SiteBundleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
