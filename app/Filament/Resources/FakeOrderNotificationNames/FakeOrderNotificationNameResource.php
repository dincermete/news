<?php

namespace App\Filament\Resources\FakeOrderNotificationNames;

use App\Filament\Resources\FakeOrderNotificationNames\Pages\CreateFakeOrderNotificationName;
use App\Filament\Resources\FakeOrderNotificationNames\Pages\EditFakeOrderNotificationName;
use App\Filament\Resources\FakeOrderNotificationNames\Pages\ListFakeOrderNotificationNames;
use App\Filament\Resources\FakeOrderNotificationNames\Schemas\FakeOrderNotificationNameForm;
use App\Filament\Resources\FakeOrderNotificationNames\Tables\FakeOrderNotificationNamesTable;
use App\Models\FakeOrderNotificationName;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FakeOrderNotificationNameResource extends Resource
{
    protected static ?string $model = FakeOrderNotificationName::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Bildirimler';

    protected static ?string $navigationLabel = 'Sahte Sipariş İsimleri';

    protected static ?string $modelLabel = 'Sahte Sipariş İsmi';

    protected static ?string $pluralModelLabel = 'Sahte Sipariş İsimleri';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FakeOrderNotificationNameForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FakeOrderNotificationNamesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFakeOrderNotificationNames::route('/'),
            'create' => CreateFakeOrderNotificationName::route('/create'),
            'edit' => EditFakeOrderNotificationName::route('/{record}/edit'),
        ];
    }
}
