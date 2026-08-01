<?php

namespace App\Filament\Resources\SiteReviews\Pages;

use App\Filament\Resources\SiteReviews\SiteReviewResource;
use Filament\Resources\Pages\ListRecords;

class ListSiteReviews extends ListRecords
{
    protected static string $resource = SiteReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
