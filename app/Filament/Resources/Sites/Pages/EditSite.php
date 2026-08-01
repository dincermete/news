<?php

namespace App\Filament\Resources\Sites\Pages;

use App\Filament\Resources\Sites\Schemas\SiteForm;
use App\Filament\Resources\Sites\SiteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class EditSite extends EditRecord
{
    protected static string $resource = SiteResource::class;

    protected static ?string $navigationLabel = 'Genel Bilgi';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?int $navigationSort = 1;

    public function form(Schema $schema): Schema
    {
        return SiteForm::configure($schema, 'general');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
