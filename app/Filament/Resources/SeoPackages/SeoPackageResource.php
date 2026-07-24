<?php

namespace App\Filament\Resources\SeoPackages;

use App\Filament\Resources\SeoPackages\Pages\CreateSeoPackage;
use App\Filament\Resources\SeoPackages\Pages\EditSeoPackage;
use App\Filament\Resources\SeoPackages\Pages\ListSeoPackages;
use App\Filament\Resources\SeoPackages\Schemas\SeoPackageForm;
use App\Filament\Resources\SeoPackages\Tables\SeoPackagesTable;
use App\Models\SeoPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SeoPackageResource extends Resource
{
    protected static ?string $model = SeoPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRocketLaunch;

    protected static string|UnitEnum|null $navigationGroup = 'Ürünler & Kampanyalar';

    protected static ?string $navigationLabel = 'SEO paketleri';

    protected static ?string $modelLabel = 'SEO paketi';

    protected static ?string $pluralModelLabel = 'SEO paketleri';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SeoPackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeoPackagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeoPackages::route('/'),
            'create' => CreateSeoPackage::route('/create'),
            'edit' => EditSeoPackage::route('/{record}/edit'),
        ];
    }
}
