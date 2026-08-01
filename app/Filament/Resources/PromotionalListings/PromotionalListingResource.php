<?php

namespace App\Filament\Resources\PromotionalListings;

use App\Filament\Resources\PromotionalListings\Pages\CreatePromotionalListing;
use App\Filament\Resources\PromotionalListings\Pages\EditPromotionalListing;
use App\Filament\Resources\PromotionalListings\Pages\ListPromotionalListings;
use App\Filament\Resources\PromotionalListings\Schemas\PromotionalListingForm;
use App\Filament\Resources\PromotionalListings\Tables\PromotionalListingsTable;
use App\Models\PromotionalListing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PromotionalListingResource extends Resource
{
    protected static ?string $model = PromotionalListing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Ürünler';

    protected static ?string $navigationLabel = 'Tanıtım Siteleri';

    protected static ?string $modelLabel = 'Tanıtım sitesi';

    protected static ?string $pluralModelLabel = 'Tanıtım siteleri';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return PromotionalListingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromotionalListingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromotionalListings::route('/'),
            'create' => CreatePromotionalListing::route('/create'),
            'edit' => EditPromotionalListing::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['site.domain', 'short_description'];
    }
}
