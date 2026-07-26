<?php

namespace App\Filament\Resources\LegalDocuments\Pages;

use App\Filament\Resources\LegalDocuments\LegalDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLegalDocument extends CreateRecord
{
    protected static string $resource = LegalDocumentResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_legal'] = true;
        $data['is_system'] = false;
        $data['route_key'] = null;
        $data['meta_title'] = $data['meta_title'] ?? $data['title'] ?? null;

        return $data;
    }
}
