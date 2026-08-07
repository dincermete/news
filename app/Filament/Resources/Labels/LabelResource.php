<?php

namespace App\Filament\Resources\Labels;

use App\Filament\Resources\Labels\Pages\ManageLabels;
use App\Filament\Resources\Labels\Schemas\LabelForm;
use App\Filament\Resources\Labels\Tables\LabelsTable;
use App\Models\Label;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LabelResource extends Resource
{
    protected static ?string $model = Label::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookmark;

    protected static string|UnitEnum|null $navigationGroup = 'Siteler';

    protected static ?string $navigationLabel = 'Etiketler';

    protected static ?string $modelLabel = 'Etiket';

    protected static ?string $pluralModelLabel = 'Etiketler';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LabelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LabelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLabels::route('/'),
        ];
    }
}
