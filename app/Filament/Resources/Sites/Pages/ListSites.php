<?php

namespace App\Filament\Resources\Sites\Pages;

use App\Filament\Imports\SiteImporter;
use App\Filament\Resources\Sites\SiteResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListSites extends ListRecords
{
    protected static string $resource = SiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(SiteImporter::class)
                ->label('İçe aktar')
                ->chunkSize(100)
                ->maxRows(5000),
            CreateAction::make(),
        ];
    }
}
