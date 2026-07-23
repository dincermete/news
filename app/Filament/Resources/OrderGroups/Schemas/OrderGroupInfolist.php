<?php

namespace App\Filament\Resources\OrderGroups\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderGroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sipariş grubu')
                    ->schema([
                        TextEntry::make('id')->label('#'),
                        TextEntry::make('user.name')->label('Müşteri'),
                        TextEntry::make('subtotal')->label('Ara toplam')->money(fn ($record) => $record->currency?->value ?? 'TRY'),
                        TextEntry::make('discount_tier_amount')->label('Kademe indirimi')->money(fn ($record) => $record->currency?->value ?? 'TRY'),
                        TextEntry::make('coupon_discount_amount')->label('Kupon indirimi')->money(fn ($record) => $record->currency?->value ?? 'TRY'),
                        TextEntry::make('total')->label('Toplam')->money(fn ($record) => $record->currency?->value ?? 'TRY'),
                        TextEntry::make('billingProfile.tax_id')->label('Fatura profili')->placeholder('—'),
                        TextEntry::make('contract_accepted_at')->label('Sözleşme')->dateTime()->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make('Siparişler')
                    ->schema([
                        RepeatableEntry::make('orders')
                            ->schema([
                                TextEntry::make('id')->label('#'),
                                TextEntry::make('product_type')->label('Ürün')->badge(),
                                TextEntry::make('site.domain')->label('Site')->placeholder('—'),
                                TextEntry::make('price')->label('Fiyat')->money(fn ($record) => $record->currency?->value ?? 'TRY'),
                                TextEntry::make('status')->label('Durum')->badge(),
                            ])
                            ->columns(5),
                    ]),
            ]);
    }
}
