<?php

namespace App\Filament\Resources\InstagramStoryPrices\Pages;

use App\Filament\Resources\InstagramStoryPrices\InstagramStoryPriceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstagramStoryPrices extends ListRecords
{
    protected static string $resource = InstagramStoryPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
