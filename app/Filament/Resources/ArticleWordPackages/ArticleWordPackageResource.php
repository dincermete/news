<?php

namespace App\Filament\Resources\ArticleWordPackages;

use App\Filament\Resources\ArticleWordPackages\Pages\CreateArticleWordPackage;
use App\Filament\Resources\ArticleWordPackages\Pages\EditArticleWordPackage;
use App\Filament\Resources\ArticleWordPackages\Pages\ListArticleWordPackages;
use App\Filament\Resources\ArticleWordPackages\Schemas\ArticleWordPackageForm;
use App\Filament\Resources\ArticleWordPackages\Tables\ArticleWordPackagesTable;
use App\Models\ArticleWordPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ArticleWordPackageResource extends Resource
{
    protected static ?string $model = ArticleWordPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Ürünler & Kampanyalar';

    protected static ?string $navigationLabel = 'Kelime paketleri';

    protected static ?string $modelLabel = 'Kelime paketi';

    protected static ?string $pluralModelLabel = 'Kelime paketleri';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'word_count';

    public static function form(Schema $schema): Schema
    {
        return ArticleWordPackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArticleWordPackagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticleWordPackages::route('/'),
            'create' => CreateArticleWordPackage::route('/create'),
            'edit' => EditArticleWordPackage::route('/{record}/edit'),
        ];
    }
}
