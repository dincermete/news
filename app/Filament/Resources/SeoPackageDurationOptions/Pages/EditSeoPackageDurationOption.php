<?php

namespace App\Filament\Resources\SeoPackageDurationOptions\Pages;

use App\Filament\Resources\SeoPackageDurationOptions\SeoPackageDurationOptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSeoPackageDurationOption extends EditRecord
{
    protected static string $resource = SeoPackageDurationOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
