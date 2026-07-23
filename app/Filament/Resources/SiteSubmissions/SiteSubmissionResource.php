<?php

namespace App\Filament\Resources\SiteSubmissions;

use App\Filament\Resources\SiteSubmissions\Pages\ListSiteSubmissions;
use App\Filament\Resources\SiteSubmissions\Pages\ViewSiteSubmission;
use App\Filament\Resources\SiteSubmissions\Schemas\SiteSubmissionInfolist;
use App\Filament\Resources\SiteSubmissions\Tables\SiteSubmissionsTable;
use App\Models\SiteSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SiteSubmissionResource extends Resource
{
    protected static ?string $model = SiteSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Envanter';

    protected static ?string $navigationLabel = 'Site Başvuruları';

    protected static ?string $modelLabel = 'Site başvurusu';

    protected static ?string $pluralModelLabel = 'Site başvuruları';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'url';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SiteSubmissionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiteSubmissionsTable::configure($table);
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
            'index' => ListSiteSubmissions::route('/'),
            'view' => ViewSiteSubmission::route('/{record}'),
        ];
    }
}
