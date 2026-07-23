<?php

namespace App\Filament\Resources\Carts;

use App\Filament\Resources\Carts\Pages\ListCarts;
use App\Filament\Resources\Carts\Pages\ViewCart;
use App\Filament\Resources\Carts\Schemas\CartInfolist;
use App\Filament\Resources\Carts\Tables\CartsTable;
use App\Models\Cart;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string|UnitEnum|null $navigationGroup = 'Siparişler';

    protected static ?string $navigationLabel = 'Sepetler';

    protected static ?string $modelLabel = 'Sepet';

    protected static ?string $pluralModelLabel = 'Sepetler';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CartInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CartsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCarts::route('/'),
            'view' => ViewCart::route('/{record}'),
        ];
    }
}
