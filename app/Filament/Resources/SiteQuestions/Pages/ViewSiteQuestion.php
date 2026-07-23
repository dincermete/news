<?php

namespace App\Filament\Resources\SiteQuestions\Pages;

use App\Filament\Resources\SiteQuestions\SiteQuestionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSiteQuestion extends ViewRecord
{
    protected static string $resource = SiteQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
