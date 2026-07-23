<?php

namespace App\Filament\Resources\DiscountTiers;

use App\Filament\Resources\DiscountTiers\Pages\CreateDiscountTier;
use App\Filament\Resources\DiscountTiers\Pages\EditDiscountTier;
use App\Filament\Resources\DiscountTiers\Pages\ListDiscountTiers;
use App\Filament\Resources\DiscountTiers\Schemas\DiscountTierForm;
use App\Filament\Resources\DiscountTiers\Tables\DiscountTiersTable;
use App\Models\DiscountTier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DiscountTierResource extends Resource
{
    protected static ?string $model = DiscountTier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|UnitEnum|null $navigationGroup = 'Ürünler & Kampanyalar';

    protected static ?string $navigationLabel = 'İndirim kademeleri';

    protected static ?string $modelLabel = 'İndirim kademesi';

    protected static ?string $pluralModelLabel = 'İndirim kademeleri';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return DiscountTierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DiscountTiersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiscountTiers::route('/'),
            'create' => CreateDiscountTier::route('/create'),
            'edit' => EditDiscountTier::route('/{record}/edit'),
        ];
    }
}
