<?php

namespace App\Filament\Resources\PanelUsers\Schemas;

use App\Enums\CustomerStatus;
use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PanelUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hesap')
                    ->schema([
                        TextInput::make('name')
                            ->label('Ad soyad')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-posta')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(32),
                        TextInput::make('password')
                            ->label('Şifre')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->helperText('Düzenlemede boş bırakılırsa şifre değişmez.'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Yetki & durum')
                    ->description('Admin paneline yalnızca Admin ve Editör rollerindeki kullanıcılar girebilir.')
                    ->schema([
                        Select::make('role')
                            ->label('Rol')
                            ->options([
                                UserRole::Admin->value => UserRole::Admin->getLabel(),
                                UserRole::Editor->value => UserRole::Editor->getLabel(),
                            ])
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText(function (Get $get): string {
                                $role = $get('role');

                                return match ($role) {
                                    UserRole::Admin->value => UserRole::Admin->panelAccessDescription(),
                                    UserRole::Editor->value => UserRole::Editor->panelAccessDescription(),
                                    default => 'Bir rol seçin.',
                                };
                            }),
                        Select::make('status')
                            ->label('Durum')
                            ->options(CustomerStatus::class)
                            ->required()
                            ->native(false)
                            ->helperText('Askıdaki hesaplar paneli kullanmamalıdır.'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
