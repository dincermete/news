<?php

namespace App\Filament\Resources\SeoPackageDurationOptions\Pages;

use App\Filament\Resources\SeoPackageDurationOptions\SeoPackageDurationOptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeoPackageDurationOptions extends ListRecords
{
    protected static string $resource = SeoPackageDurationOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
