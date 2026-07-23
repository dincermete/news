<?php

namespace App\Filament\Resources\ApiTokens\Schemas;

use App\Enums\ApiTokenAbility;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('API Anahtarı')
                    ->schema([
                        Select::make('user_id')
                            ->label('Kullanıcı')
                            ->options(fn (): array => User::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required()
                            ->visibleOn('create'),
                        TextInput::make('user_name')
                            ->label('Kullanıcı')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                        TextInput::make('name')
                            ->label('Anahtar adı')
                            ->required()
                            ->maxLength(255)
                            ->disabledOn('edit'),
                        CheckboxList::make('abilities')
                            ->label('Yetkiler')
                            ->options(ApiTokenAbility::options())
                            ->columns(2)
                            ->required()
                            ->disabledOn('edit')
                            ->dehydrated(fn (string $operation): bool => $operation === 'create'),
                        TextInput::make('last_used_at')
                            ->label('Son kullanım')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit')
                            ->formatStateUsing(function (mixed $state, ?PersonalAccessToken $record): ?string {
                                return $record?->last_used_at?->format('d.m.Y H:i');
                            }),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
