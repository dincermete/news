<?php

namespace App\Filament\Resources\PromotionalListings\Pages;

use App\Filament\Resources\PromotionalListings\PromotionalListingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPromotionalListing extends EditRecord
{
    protected static string $resource = PromotionalListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
