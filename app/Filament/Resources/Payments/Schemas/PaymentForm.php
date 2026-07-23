<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('paymentTabs')
                    ->tabs([
                        Tab::make('Ödeme')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('order_id')
                                        ->label('Sipariş')
                                        ->relationship('order', 'id')
                                        ->getOptionLabelFromRecordUsing(fn ($record) => '#'.$record->id.' — '.($record->user?->name ?? 'Müşteri'))
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    TextInput::make('amount')
                                        ->label('Tutar')
                                        ->numeric()
                                        ->required()
                                        ->minValue(0)
                                        ->step(0.01),
                                    Select::make('currency')
                                        ->label('Para birimi')
                                        ->options(Currency::class)
                                        ->required()
                                        ->default(Currency::Try),
                                    Select::make('method')
                                        ->label('Yöntem')
                                        ->options(PaymentMethod::class)
                                        ->required()
                                        ->live(),
                                    Select::make('status')
                                        ->label('Durum')
                                        ->options(PaymentStatus::class)
                                        ->required()
                                        ->default(PaymentStatus::Pending)
                                        ->helperText('Havale onayları için tablo aksiyonunu kullanın.'),
                                    DateTimePicker::make('paid_at')
                                        ->label('Ödeme zamanı')
                                        ->seconds(false),
                                ]),
                            ]),
                        Tab::make('PayTR / Dekont')
                            ->icon(Heroicon::OutlinedDocumentArrowUp)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('paytr_merchant_oid')
                                        ->label('PayTR merchant OID')
                                        ->maxLength(64),
                                    TextInput::make('paytr_token')
                                        ->label('PayTR token')
                                        ->columnSpanFull(),
                                    FileUpload::make('receipt_path')
                                        ->label('Havale dekontu')
                                        ->disk('local')
                                        ->directory('receipts')
                                        ->acceptedFileTypes([
                                            'application/pdf',
                                            'image/jpeg',
                                            'image/png',
                                            'image/webp',
                                        ])
                                        ->visible(fn ($get): bool => $get('method') === PaymentMethod::BankTransfer->value
                                            || $get('method') === PaymentMethod::BankTransfer)
                                        ->columnSpanFull(),
                                ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }
}
