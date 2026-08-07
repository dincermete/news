<?php

namespace App\Filament\Resources\SiteCategories;

use App\Filament\Resources\SiteCategories\Pages\ManageSiteCategories;
use App\Filament\Resources\SiteCategories\Schemas\SiteCategoryForm;
use App\Filament\Resources\SiteCategories\Tables\SiteCategoriesTable;
use App\Models\SiteCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SiteCategoryResource extends Resource
{
    protected static ?string $model = SiteCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Siteler';

    protected static ?string $navigationLabel = 'Kategoriler';

    protected static ?string $modelLabel = 'Kategori';

    protected static ?string $pluralModelLabel = 'Kategoriler';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SiteCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiteCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSiteCategories::route('/'),
        ];
    }
}
