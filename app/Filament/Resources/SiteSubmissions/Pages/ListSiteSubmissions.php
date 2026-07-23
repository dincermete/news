<?php

namespace App\Filament\Resources\SiteSubmissions\Pages;

use App\Filament\Resources\SiteSubmissions\SiteSubmissionResource;
use Filament\Resources\Pages\ListRecords;

class ListSiteSubmissions extends ListRecords
{
    protected static string $resource = SiteSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
