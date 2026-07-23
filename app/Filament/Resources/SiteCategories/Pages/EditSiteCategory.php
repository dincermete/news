<?php

namespace App\Filament\Resources\SiteCategories\Pages;

use App\Filament\Resources\SiteCategories\SiteCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSiteCategory extends EditRecord
{
    protected static string $resource = SiteCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
