<?php

namespace App\Filament\Resources\LegalDocuments;

use App\Filament\Resources\LegalDocuments\Pages\CreateLegalDocument;
use App\Filament\Resources\LegalDocuments\Pages\EditLegalDocument;
use App\Filament\Resources\LegalDocuments\Pages\ListLegalDocuments;
use App\Filament\Resources\LegalDocuments\Schemas\LegalDocumentForm;
use App\Filament\Resources\LegalDocuments\Tables\LegalDocumentsTable;
use App\Models\Page;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LegalDocumentResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'İçerik';

    protected static ?string $navigationLabel = 'Sözleşmeler';

    protected static ?string $modelLabel = 'Sözleşme';

    protected static ?string $pluralModelLabel = 'Sözleşmeler';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $slug = 'sozlesmeler';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_legal', true);
    }

    public static function form(Schema $schema): Schema
    {
        return LegalDocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LegalDocumentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegalDocuments::route('/'),
            'create' => CreateLegalDocument::route('/create'),
            'edit' => EditLegalDocument::route('/{record}/edit'),
        ];
    }
}
