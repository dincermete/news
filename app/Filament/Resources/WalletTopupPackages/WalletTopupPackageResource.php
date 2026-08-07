<?php

namespace App\Filament\Resources\WalletTopupPackages;

use App\Filament\Resources\WalletTopupPackages\Pages\ManageWalletTopupPackages;
use App\Filament\Resources\WalletTopupPackages\Schemas\WalletTopupPackageForm;
use App\Filament\Resources\WalletTopupPackages\Tables\WalletTopupPackagesTable;
use App\Models\WalletTopupPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WalletTopupPackageResource extends Resource
{
    protected static ?string $model = WalletTopupPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static string|UnitEnum|null $navigationGroup = 'Kampanyalar';

    protected static ?string $navigationLabel = 'Bakiye Paketleri';

    protected static ?string $modelLabel = 'Bakiye paketi';

    protected static ?string $pluralModelLabel = 'Bakiye paketleri';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'amount';

    public static function form(Schema $schema): Schema
    {
        return WalletTopupPackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WalletTopupPackagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageWalletTopupPackages::route('/'),
        ];
    }
}
