<?php

namespace App\Filament\Resources\SpinWheelPrizes;

use App\Filament\Resources\SpinWheelPrizes\Pages\CreateSpinWheelPrize;
use App\Filament\Resources\SpinWheelPrizes\Pages\EditSpinWheelPrize;
use App\Filament\Resources\SpinWheelPrizes\Pages\ListSpinWheelPrizes;
use App\Filament\Resources\SpinWheelPrizes\Schemas\SpinWheelPrizeForm;
use App\Filament\Resources\SpinWheelPrizes\Tables\SpinWheelPrizesTable;
use App\Models\SpinWheelPrize;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SpinWheelPrizeResource extends Resource
{
    protected static ?string $model = SpinWheelPrize::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Çark & Bakiye';

    protected static ?string $navigationLabel = 'Çark ödülleri';

    protected static ?string $modelLabel = 'Çark ödülü';

    protected static ?string $pluralModelLabel = 'Çark ödülleri';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SpinWheelPrizeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpinWheelPrizesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpinWheelPrizes::route('/'),
            'create' => CreateSpinWheelPrize::route('/create'),
            'edit' => EditSpinWheelPrize::route('/{record}/edit'),
        ];
    }
}
