<?php

namespace App\Filament\Resources\OrderGroups;

use App\Filament\Resources\OrderGroups\Pages\ListOrderGroups;
use App\Filament\Resources\OrderGroups\Pages\ViewOrderGroup;
use App\Filament\Resources\OrderGroups\Schemas\OrderGroupInfolist;
use App\Filament\Resources\OrderGroups\Tables\OrderGroupsTable;
use App\Models\OrderGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class OrderGroupResource extends Resource
{
    protected static ?string $model = OrderGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Siparişler';

    protected static ?string $navigationLabel = 'Sipariş grupları';

    protected static ?string $modelLabel = 'Sipariş grubu';

    protected static ?string $pluralModelLabel = 'Sipariş grupları';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderGroupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderGroupsTable::configure($table);
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
            'index' => ListOrderGroups::route('/'),
            'view' => ViewOrderGroup::route('/{record}'),
        ];
    }
}
