<?php

namespace App\Filament\Resources\SiteReviews\Pages;

use App\Filament\Resources\SiteReviews\SiteReviewResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSiteReview extends ViewRecord
{
    protected static string $resource = SiteReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
