<?php

namespace App\Filament\Resources\BacklinkPackages;

use App\Filament\Resources\BacklinkPackages\Pages\CreateBacklinkPackage;
use App\Filament\Resources\BacklinkPackages\Pages\EditBacklinkPackage;
use App\Filament\Resources\BacklinkPackages\Pages\ListBacklinkPackages;
use App\Filament\Resources\BacklinkPackages\Schemas\BacklinkPackageForm;
use App\Filament\Resources\BacklinkPackages\Tables\BacklinkPackagesTable;
use App\Models\BacklinkPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BacklinkPackageResource extends Resource
{
    protected static ?string $model = BacklinkPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static string|UnitEnum|null $navigationGroup = 'Ürünler & Kampanyalar';

    protected static ?string $navigationLabel = 'Backlink paketleri';

    protected static ?string $modelLabel = 'Backlink paketi';

    protected static ?string $pluralModelLabel = 'Backlink paketleri';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BacklinkPackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BacklinkPackagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBacklinkPackages::route('/'),
            'create' => CreateBacklinkPackage::route('/create'),
            'edit' => EditBacklinkPackage::route('/{record}/edit'),
        ];
    }
}
