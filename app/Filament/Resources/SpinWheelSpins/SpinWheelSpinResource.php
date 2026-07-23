<?php

namespace App\Filament\Resources\SpinWheelSpins;

use App\Filament\Resources\SpinWheelSpins\Pages\ListSpinWheelSpins;
use App\Filament\Resources\SpinWheelSpins\Tables\SpinWheelSpinsTable;
use App\Models\SpinWheelSpin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SpinWheelSpinResource extends Resource
{
    protected static ?string $model = SpinWheelSpin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static string|UnitEnum|null $navigationGroup = 'Çark & Bakiye';

    protected static ?string $navigationLabel = 'Çark çevirileri';

    protected static ?string $modelLabel = 'Çark çevirisi';

    protected static ?string $pluralModelLabel = 'Çark çevirileri';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return SpinWheelSpinsTable::configure($table);
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
            'index' => ListSpinWheelSpins::route('/'),
        ];
    }
}
