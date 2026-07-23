<?php

namespace App\Filament\Resources\ArticleWordPackages\Pages;

use App\Filament\Resources\ArticleWordPackages\ArticleWordPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArticleWordPackages extends ListRecords
{
    protected static string $resource = ArticleWordPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
