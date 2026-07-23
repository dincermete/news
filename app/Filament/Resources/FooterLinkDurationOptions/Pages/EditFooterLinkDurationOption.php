<?php

namespace App\Filament\Resources\FooterLinkDurationOptions\Pages;

use App\Filament\Resources\FooterLinkDurationOptions\FooterLinkDurationOptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFooterLinkDurationOption extends EditRecord
{
    protected static string $resource = FooterLinkDurationOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
