<?php

namespace App\Filament\Resources\SiteCategories\Pages;

use App\Filament\Resources\SiteCategories\SiteCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSiteCategories extends ListRecords
{
    protected static string $resource = SiteCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
