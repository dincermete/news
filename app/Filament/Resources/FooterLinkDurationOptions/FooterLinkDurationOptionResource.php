<?php

namespace App\Filament\Resources\FooterLinkDurationOptions;

use App\Filament\Resources\FooterLinkDurationOptions\Pages\CreateFooterLinkDurationOption;
use App\Filament\Resources\FooterLinkDurationOptions\Pages\EditFooterLinkDurationOption;
use App\Filament\Resources\FooterLinkDurationOptions\Pages\ListFooterLinkDurationOptions;
use App\Filament\Resources\FooterLinkDurationOptions\Schemas\FooterLinkDurationOptionForm;
use App\Filament\Resources\FooterLinkDurationOptions\Tables\FooterLinkDurationOptionsTable;
use App\Models\FooterLinkDurationOption;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FooterLinkDurationOptionResource extends Resource
{
    protected static ?string $model = FooterLinkDurationOption::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static string|UnitEnum|null $navigationGroup = 'Ürünler & Kampanyalar';

    protected static ?string $navigationLabel = 'Footer süreleri';

    protected static ?string $modelLabel = 'Footer süre seçeneği';

    protected static ?string $pluralModelLabel = 'Footer süre seçenekleri';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FooterLinkDurationOptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FooterLinkDurationOptionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFooterLinkDurationOptions::route('/'),
            'create' => CreateFooterLinkDurationOption::route('/create'),
            'edit' => EditFooterLinkDurationOption::route('/{record}/edit'),
        ];
    }
}
