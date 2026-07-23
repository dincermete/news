<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\Currency;
use App\Enums\WalletBalanceType;
use App\Models\User;
use App\Models\Wallet;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Genel Bilgi')
                    ->schema([
                        TextEntry::make('name')->label('Ad soyad'),
                        TextEntry::make('email')->label('E-posta'),
                        TextEntry::make('phone')->label('Telefon')->placeholder('—'),
                        TextEntry::make('affiliate_code')->label('Affiliate kodu')->placeholder('—'),
                        TextEntry::make('affiliate_commission_rate')
                            ->label('Komisyon oranı')
                            ->formatStateUsing(fn ($state): string => $state === null
                                ? 'Oran bekleniyor'
                                : number_format((float) $state, 2, ',', '.').'%'),
                        TextEntry::make('status')->label('Durum')->badge(),
                        TextEntry::make('created_at')->label('Kayıt tarihi')->dateTime('d.m.Y H:i'),
                        TextEntry::make('total_spent')
                            ->label('Toplam harcama')
                            ->state(fn (User $record): string => number_format($record->totalSpent(), 2, ',', '.').' ₺'),
                    ])
                    ->columns(2),
                Section::make('Bakiye Detayı')
                    ->schema(
                        collect(WalletBalanceType::cases())
                            ->map(fn (WalletBalanceType $type): TextEntry => TextEntry::make('bucket_'.$type->value)
                                ->label($type->getLabel())
                                ->state(function (User $record) use ($type): string {
                                    $wallet = Wallet::forUser($record, Currency::Try);

                                    return number_format($wallet->bucketBalance($type), 2, ',', '.').' ₺';
                                }))
                            ->all()
                    )
                    ->columns(2),
            ]);
    }
}
