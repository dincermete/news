<?php

namespace App\Filament\Resources\ApiTokens;

use App\Filament\Resources\ApiTokens\Pages\CreateApiToken;
use App\Filament\Resources\ApiTokens\Pages\EditApiToken;
use App\Filament\Resources\ApiTokens\Pages\ListApiTokens;
use App\Filament\Resources\ApiTokens\Schemas\ApiTokenForm;
use App\Filament\Resources\ApiTokens\Tables\ApiTokensTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Laravel\Sanctum\PersonalAccessToken;
use UnitEnum;

class ApiTokenResource extends Resource
{
    protected static ?string $model = PersonalAccessToken::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'API Anahtarları';

    protected static ?string $modelLabel = 'API Anahtarı';

    protected static ?string $pluralModelLabel = 'API Anahtarları';

    protected static ?int $navigationSort = 90;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ApiTokenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApiTokensTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApiTokens::route('/'),
            'create' => CreateApiToken::route('/create'),
            'edit' => EditApiToken::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('tokenable_type', \App\Models\User::class)
            ->with('tokenable');
    }
}
