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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $plainPassword = Str::password(12);

        $this->plainPassword = $plainPassword;

        $data['password'] = Hash::make($plainPassword);
        $data['role'] = UserRole::Customer;
        $data['status'] = CustomerStatus::Active;
        $data['email_verified_at'] = now();

        return $data;
    }

    protected string $plainPassword = '';

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
