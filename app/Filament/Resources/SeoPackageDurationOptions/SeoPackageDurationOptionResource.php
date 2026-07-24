<?php

namespace App\Filament\Resources\SeoPackageDurationOptions;

use App\Filament\Resources\SeoPackageDurationOptions\Pages\CreateSeoPackageDurationOption;
use App\Filament\Resources\SeoPackageDurationOptions\Pages\EditSeoPackageDurationOption;
use App\Filament\Resources\SeoPackageDurationOptions\Pages\ListSeoPackageDurationOptions;
use App\Filament\Resources\SeoPackageDurationOptions\Schemas\SeoPackageDurationOptionForm;
use App\Filament\Resources\SeoPackageDurationOptions\Tables\SeoPackageDurationOptionsTable;
use App\Models\SeoPackageDurationOption;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SeoPackageDurationOptionResource extends Resource
{
    protected static ?string $model = SeoPackageDurationOption::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Ürünler & Kampanyalar';

    protected static ?string $navigationLabel = 'SEO paketi süreleri';

    protected static ?string $modelLabel = 'SEO paketi süre seçeneği';

    protected static ?string $pluralModelLabel = 'SEO paketi süre seçenekleri';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SeoPackageDurationOptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeoPackageDurationOptionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeoPackageDurationOptions::route('/'),
            'create' => CreateSeoPackageDurationOption::route('/create'),
            'edit' => EditSeoPackageDurationOption::route('/{record}/edit'),
        ];
    }
}
