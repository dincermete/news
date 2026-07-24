<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Banka adı')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),
                    TextInput::make('short_code')
                        ->label('Kısa kod')
                        ->maxLength(10)
                        ->helperText('Örn. "QNB", "ZIR" — bildirim sayfasında rozet olarak gösterilir.')
                        ->columnSpan(1),
                    TextInput::make('account_name')
                        ->label('Hesap adı')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('iban')
                        ->label('IBAN')
                        ->required()
                        ->maxLength(64)
                        ->columnSpanFull(),
                    TextInput::make('sort_order')
                        ->label('Sıra')
                        ->numeric()
                        ->default(0)
                        ->columnSpan(1),
                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true)
                        ->inline(false)
                        ->columnSpan(1),
                ]),
            ]);
    }
}
