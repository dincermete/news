<?php

namespace App\Filament\Resources\SeoAnalysisRequests\Pages;

use App\Enums\SeoAnalysisStatus;
use App\Filament\Resources\SeoAnalysisRequests\SeoAnalysisRequestResource;
use Filament\Resources\Pages\EditRecord;

class EditSeoAnalysisRequest extends EditRecord
{
    protected static string $resource = SeoAnalysisRequestResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['status'] === SeoAnalysisStatus::Completed->value && $this->record->completed_at === null) {
            $data['completed_at'] = now();
        }

        return $data;
    }
}
