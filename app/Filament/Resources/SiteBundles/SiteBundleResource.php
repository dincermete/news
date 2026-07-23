<?php

namespace App\Filament\Resources\SiteBundles;

use App\Filament\Resources\SiteBundles\Pages\CreateSiteBundle;
use App\Filament\Resources\SiteBundles\Pages\EditSiteBundle;
use App\Filament\Resources\SiteBundles\Pages\ListSiteBundles;
use App\Filament\Resources\SiteBundles\Schemas\SiteBundleForm;
use App\Filament\Resources\SiteBundles\Tables\SiteBundlesTable;
use App\Models\SiteBundle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SiteBundleResource extends Resource
{
    protected static ?string $model = SiteBundle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Ürünler & Kampanyalar';

    protected static ?string $navigationLabel = 'Site paketleri';

    protected static ?string $modelLabel = 'Site paketi';

    protected static ?string $pluralModelLabel = 'Site paketleri';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SiteBundleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiteBundlesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteBundles::route('/'),
            'create' => CreateSiteBundle::route('/create'),
            'edit' => EditSiteBundle::route('/{record}/edit'),
        ];
    }
}
