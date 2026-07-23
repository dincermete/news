<?php

namespace App\Filament\Resources\FaqEntries;

use App\Filament\Resources\FaqEntries\Pages\CreateFaqEntry;
use App\Filament\Resources\FaqEntries\Pages\EditFaqEntry;
use App\Filament\Resources\FaqEntries\Pages\ListFaqEntries;
use App\Filament\Resources\FaqEntries\Schemas\FaqEntryForm;
use App\Filament\Resources\FaqEntries\Tables\FaqEntriesTable;
use App\Models\FaqEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FaqEntryResource extends Resource
{
    protected static ?string $model = FaqEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Bildirimler';

    protected static ?string $navigationLabel = 'SSS';

    protected static ?string $modelLabel = 'SSS kaydı';

    protected static ?string $pluralModelLabel = 'SSS';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'question_topic';

    public static function form(Schema $schema): Schema
    {
        return FaqEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FaqEntriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFaqEntries::route('/'),
            'create' => CreateFaqEntry::route('/create'),
            'edit' => EditFaqEntry::route('/{record}/edit'),
        ];
    }
}
