<?php

namespace App\Filament\Resources\SiteReviews;

use App\Filament\Resources\SiteReviews\Pages\EditSiteReview;
use App\Filament\Resources\SiteReviews\Pages\ListSiteReviews;
use App\Filament\Resources\SiteReviews\Pages\ViewSiteReview;
use App\Filament\Resources\SiteReviews\Schemas\SiteReviewForm;
use App\Filament\Resources\SiteReviews\Schemas\SiteReviewInfolist;
use App\Filament\Resources\SiteReviews\Tables\SiteReviewsTable;
use App\Models\SiteReview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SiteReviewResource extends Resource
{
    protected static ?string $model = SiteReview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'Destek';

    protected static ?string $navigationLabel = 'Site Yorumları';

    protected static ?string $modelLabel = 'Site yorumu';

    protected static ?string $pluralModelLabel = 'Site Yorumları';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SiteReviewForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SiteReviewInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiteReviewsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteReviews::route('/'),
            'view' => ViewSiteReview::route('/{record}'),
            'edit' => EditSiteReview::route('/{record}/edit'),
        ];
    }
}
