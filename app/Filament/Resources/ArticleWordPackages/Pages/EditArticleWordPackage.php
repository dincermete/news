<?php

namespace App\Filament\Resources\ArticleWordPackages\Pages;

use App\Filament\Resources\ArticleWordPackages\ArticleWordPackageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArticleWordPackage extends EditRecord
{
    protected static string $resource = ArticleWordPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
