<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\CustomerStatus;
use App\Enums\UserRole;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;

class CustomerForm
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
                            ->required(false)
                            ->minLength(8)
                            ->helperText(fn (string $operation): string => $operation === 'create'
                                ? 'Boş bırakılırsa geçici şifre otomatik üretilir.'
                                : 'Boş bırakılırsa mevcut şifre korunur.'),
                        Select::make('status')
                            ->label('Durum')
                            ->options(CustomerStatus::class)
                            ->required()
                            ->native(false)
                            ->default(CustomerStatus::Active),
                        DateTimePicker::make('email_verified_at')
                            ->label('E-posta doğrulama')
                            ->seconds(false)
                            ->native(false)
                            ->helperText('Boşsa e-posta doğrulanmamış sayılır.'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('İletişim izinleri')
                    ->schema([
                        Toggle::make('email_consent')
                            ->label('E-posta izni')
                            ->default(false),
                        Toggle::make('sms_consent')
                            ->label('SMS izni')
                            ->default(false),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Affiliate')
                    ->schema([
                        TextInput::make('affiliate_code')
                            ->label('Affiliate kodu')
                            ->maxLength(64)
                            ->unique(ignoreRecord: true)
                            ->helperText('Boş bırakılabilir; sistem ilk erişimde üretebilir.'),
                        TextInput::make('affiliate_commission_rate')
                            ->label('Komisyon oranı')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->helperText('Boş bırakılırsa oran bekleniyor; komisyon işlenmez.'),
                        Select::make('referred_by_id')
                            ->label('Referans müşteri')
                            ->relationship(
                                name: 'referrer',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query
                                    ->where('role', UserRole::Customer)
                                    ->orderBy('name'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (User $record): string => "{$record->name} ({$record->email})"
                            )
                            ->searchable(['name', 'email'])
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function modalWidth(): Width
    {
        return Width::TwoExtraLarge;
    }
}
