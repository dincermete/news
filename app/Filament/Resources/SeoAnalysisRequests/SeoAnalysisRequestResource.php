<?php

namespace App\Filament\Resources\SeoAnalysisRequests;

use App\Filament\Resources\SeoAnalysisRequests\Pages\EditSeoAnalysisRequest;
use App\Filament\Resources\SeoAnalysisRequests\Pages\ListSeoAnalysisRequests;
use App\Filament\Resources\SeoAnalysisRequests\Schemas\SeoAnalysisRequestForm;
use App\Filament\Resources\SeoAnalysisRequests\Tables\SeoAnalysisRequestsTable;
use App\Models\SeoAnalysisRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SeoAnalysisRequestResource extends Resource
{
    protected static ?string $model = SeoAnalysisRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlassCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Destek & Talepler';

    protected static ?string $navigationLabel = 'SEO Analiz Talepleri';

    protected static ?string $modelLabel = 'SEO analiz talebi';

    protected static ?string $pluralModelLabel = 'SEO analiz talepleri';

    public static function form(Schema $schema): Schema
    {
        return SeoAnalysisRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeoAnalysisRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeoAnalysisRequests::route('/'),
            'edit' => EditSeoAnalysisRequest::route('/{record}/edit'),
        ];
    }
}
