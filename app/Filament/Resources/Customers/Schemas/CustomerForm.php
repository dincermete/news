<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Müşteri bilgileri')
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
                        TextInput::make('affiliate_code')
                            ->label('Affiliate kodu')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('İlk erişimde üretilir'),
                        TextInput::make('affiliate_commission_rate')
                            ->label('Affiliate komisyon oranı')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->helperText('Boş bırakılırsa oran bekleniyor; komisyon işlenmez.'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
