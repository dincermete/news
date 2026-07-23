<?php

namespace App\Filament\Resources\SiteQuestions\Pages;

use App\Filament\Resources\SiteQuestions\SiteQuestionResource;
use Filament\Resources\Pages\ListRecords;

class ListSiteQuestions extends ListRecords
{
    protected static string $resource = SiteQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
