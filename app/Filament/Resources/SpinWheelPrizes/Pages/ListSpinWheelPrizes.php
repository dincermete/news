<?php

namespace App\Filament\Resources\SpinWheelPrizes\Pages;

use App\Filament\Resources\SpinWheelPrizes\SpinWheelPrizeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpinWheelPrizes extends ListRecords
{
    protected static string $resource = SpinWheelPrizeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
