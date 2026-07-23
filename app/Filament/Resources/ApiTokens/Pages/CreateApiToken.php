<?php

namespace App\Filament\Resources\ApiTokens\Pages;

use App\Filament\Resources\ApiTokens\ApiTokenResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\NewAccessToken;

class CreateApiToken extends CreateRecord
{
    protected static string $resource = ApiTokenResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var User $user */
        $user = User::query()->findOrFail($data['user_id']);

        /** @var list<string> $abilities */
        $abilities = array_values($data['abilities'] ?? []);

        /** @var NewAccessToken $token */
        $token = $user->createToken((string) $data['name'], $abilities);

        Notification::make()
            ->title('API anahtarı oluşturuldu')
            ->body('Bu anahtar yalnızca bir kez gösterilir. Kopyalayın: '.$token->plainTextToken)
            ->success()
            ->persistent()
            ->send();

        return $token->accessToken;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
