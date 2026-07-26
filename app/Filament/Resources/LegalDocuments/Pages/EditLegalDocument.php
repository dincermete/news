<?php

namespace App\Filament\Resources\LegalDocuments\Pages;

use App\Filament\Resources\LegalDocuments\LegalDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLegalDocument extends EditRecord
{
    protected static string $resource = LegalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['is_legal'] = true;
        $data['is_system'] = false;

        return $data;
    }
}
