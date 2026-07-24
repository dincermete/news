<?php

namespace App\Filament\Resources\InstagramStoryPrices\Pages;

use App\Filament\Resources\InstagramStoryPrices\InstagramStoryPriceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInstagramStoryPrice extends EditRecord
{
    protected static string $resource = InstagramStoryPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
