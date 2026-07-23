<?php

namespace App\Filament\Resources\DiscountTiers\Pages;

use App\Filament\Resources\DiscountTiers\DiscountTierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDiscountTiers extends ListRecords
{
    protected static string $resource = DiscountTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
