<?php

namespace App\Filament\Resources\SpinWheelPrizes\Pages;

use App\Filament\Resources\SpinWheelPrizes\SpinWheelPrizeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSpinWheelPrize extends EditRecord
{
    protected static string $resource = SpinWheelPrizeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
