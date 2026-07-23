<?php

namespace App\Filament\Resources\SpinCreditTransactions;

use App\Filament\Resources\SpinCreditTransactions\Pages\ListSpinCreditTransactions;
use App\Filament\Resources\SpinCreditTransactions\Tables\SpinCreditTransactionsTable;
use App\Models\SpinCreditTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SpinCreditTransactionResource extends Resource
{
    protected static ?string $model = SpinCreditTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|UnitEnum|null $navigationGroup = 'Çark & Bakiye';

    protected static ?string $navigationLabel = 'Çark kredileri';

    protected static ?string $modelLabel = 'Çark kredisi';

    protected static ?string $pluralModelLabel = 'Çark kredileri';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return SpinCreditTransactionsTable::configure($table);
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpinCreditTransactions::route('/'),
        ];
    }
}
