<?php

namespace App\Filament\Resources\InstagramStoryPrices;

use App\Filament\Resources\InstagramStoryPrices\Pages\CreateInstagramStoryPrice;
use App\Filament\Resources\InstagramStoryPrices\Pages\EditInstagramStoryPrice;
use App\Filament\Resources\InstagramStoryPrices\Pages\ListInstagramStoryPrices;
use App\Filament\Resources\InstagramStoryPrices\Schemas\InstagramStoryPriceForm;
use App\Filament\Resources\InstagramStoryPrices\Tables\InstagramStoryPricesTable;
use App\Models\InstagramStoryPrice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InstagramStoryPriceResource extends Resource
{
    protected static ?string $model = InstagramStoryPrice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCamera;

    protected static string|UnitEnum|null $navigationGroup = 'Ürünler & Kampanyalar';

    protected static ?string $navigationLabel = 'Story fiyatları';

    protected static ?string $modelLabel = 'Story fiyatı';

    protected static ?string $pluralModelLabel = 'Story fiyatları';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return InstagramStoryPriceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstagramStoryPricesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstagramStoryPrices::route('/'),
            'create' => CreateInstagramStoryPrice::route('/create'),
            'edit' => EditInstagramStoryPrice::route('/{record}/edit'),
        ];
    }
}
