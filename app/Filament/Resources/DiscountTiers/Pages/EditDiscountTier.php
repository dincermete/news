<?php

namespace App\Filament\Resources\DiscountTiers\Pages;

use App\Filament\Resources\DiscountTiers\DiscountTierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDiscountTier extends EditRecord
{
    protected static string $resource = DiscountTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
