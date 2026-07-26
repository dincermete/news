<?php

namespace App\Filament\Resources\PanelUsers\Pages;

use App\Enums\CustomerStatus;
use App\Enums\UserRole;
use App\Filament\Resources\PanelUsers\PanelUserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreatePanelUser extends CreateRecord
{
    protected static string $resource = PanelUserResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $data['role'] = $data['role'] ?? UserRole::Editor->value;
        $data['status'] = $data['status'] ?? CustomerStatus::Active->value;
        $data['email_verified_at'] = now();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
