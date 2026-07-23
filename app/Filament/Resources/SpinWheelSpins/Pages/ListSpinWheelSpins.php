<?php

namespace App\Filament\Resources\SpinWheelSpins\Pages;

use App\Filament\Resources\SpinWheelSpins\SpinWheelSpinResource;
use Filament\Resources\Pages\ListRecords;

class ListSpinWheelSpins extends ListRecords
{
    protected static string $resource = SpinWheelSpinResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
