<?php

namespace App\Filament\Resources\SiteQuestions\Pages;

use App\Filament\Resources\SiteQuestions\SiteQuestionResource;
use App\Models\SiteQuestion;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSiteQuestion extends EditRecord
{
    protected static string $resource = SiteQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var SiteQuestion $record */
        $record = $this->getRecord();

        if (filled($data['answer'] ?? null) && blank($record->answered_at)) {
            $data['answered_by'] = Auth::id();
            $data['answered_at'] = now();
        }

        return $data;
    }
}
