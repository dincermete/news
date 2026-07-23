<?php

namespace App\Filament\Resources\FooterLinkDurationOptions\Pages;

use App\Filament\Resources\FooterLinkDurationOptions\FooterLinkDurationOptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFooterLinkDurationOptions extends ListRecords
{
    protected static string $resource = FooterLinkDurationOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
