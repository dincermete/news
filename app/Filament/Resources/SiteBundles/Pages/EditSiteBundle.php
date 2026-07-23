<?php

namespace App\Filament\Resources\SiteBundles\Pages;

use App\Filament\Resources\SiteBundles\SiteBundleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSiteBundle extends EditRecord
{
    protected static string $resource = SiteBundleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
