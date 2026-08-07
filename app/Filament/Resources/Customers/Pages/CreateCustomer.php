<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Enums\CustomerStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected string $plainPassword = '';

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (filled($data['password'] ?? null)) {
            $this->plainPassword = (string) $data['password'];
            $data['password'] = Hash::make($this->plainPassword);
        } else {
            $this->plainPassword = Str::password(12);
            $data['password'] = Hash::make($this->plainPassword);
        }

        $data['role'] = UserRole::Customer;
        $data['status'] = $data['status'] ?? CustomerStatus::Active;
        $data['email_verified_at'] = $data['email_verified_at'] ?? now();

        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Müşteri oluşturuldu')
            ->body('Geçici şifre (bir kez gösterilir): '.$this->plainPassword)
            ->success()
            ->persistent()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
