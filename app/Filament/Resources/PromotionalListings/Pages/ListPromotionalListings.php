<?php

namespace App\Filament\Resources\PromotionalListings\Pages;

use App\Filament\Resources\PromotionalListings\PromotionalListingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPromotionalListings extends ListRecords
{
    protected static string $resource = PromotionalListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
