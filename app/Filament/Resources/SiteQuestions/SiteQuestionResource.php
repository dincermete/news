<?php

namespace App\Filament\Resources\SiteQuestions;

use App\Filament\Resources\SiteQuestions\Pages\EditSiteQuestion;
use App\Filament\Resources\SiteQuestions\Pages\ListSiteQuestions;
use App\Filament\Resources\SiteQuestions\Pages\ViewSiteQuestion;
use App\Filament\Resources\SiteQuestions\Schemas\SiteQuestionForm;
use App\Filament\Resources\SiteQuestions\Schemas\SiteQuestionInfolist;
use App\Filament\Resources\SiteQuestions\Tables\SiteQuestionsTable;
use App\Models\SiteQuestion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SiteQuestionResource extends Resource
{
    protected static ?string $model = SiteQuestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Bildirimler';

    protected static ?string $navigationLabel = 'Site Soruları';

    protected static ?string $modelLabel = 'Site sorusu';

    protected static ?string $pluralModelLabel = 'Site Soruları';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'question';

    public static function form(Schema $schema): Schema
    {
        return SiteQuestionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SiteQuestionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiteQuestionsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteQuestions::route('/'),
            'view' => ViewSiteQuestion::route('/{record}'),
            'edit' => EditSiteQuestion::route('/{record}/edit'),
        ];
    }
}
