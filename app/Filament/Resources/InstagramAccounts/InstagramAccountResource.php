<?php

namespace App\Filament\Resources\InstagramAccounts;

use App\Filament\Resources\InstagramAccounts\Pages\CreateInstagramAccount;
use App\Filament\Resources\InstagramAccounts\Pages\EditInstagramAccount;
use App\Filament\Resources\InstagramAccounts\Pages\ListInstagramAccounts;
use App\Filament\Resources\InstagramAccounts\Schemas\InstagramAccountForm;
use App\Filament\Resources\InstagramAccounts\Tables\InstagramAccountsTable;
use App\Models\InstagramAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InstagramAccountResource extends Resource
{
    protected static ?string $model = InstagramAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAtSymbol;

    protected static string|UnitEnum|null $navigationGroup = 'Ürünler & Kampanyalar';

    protected static ?string $navigationLabel = 'Instagram hesapları';

    protected static ?string $modelLabel = 'Instagram hesabı';

    protected static ?string $pluralModelLabel = 'Instagram hesapları';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'handle';

    public static function form(Schema $schema): Schema
    {
        return InstagramAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstagramAccountsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstagramAccounts::route('/'),
            'create' => CreateInstagramAccount::route('/create'),
            'edit' => EditInstagramAccount::route('/{record}/edit'),
        ];
    }
}
