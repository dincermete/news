<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\ContentSource;
use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('orderTabs')
                    ->tabs([
                        Tab::make('Sipariş Bilgisi')
                            ->icon(Heroicon::OutlinedClipboardDocumentList)
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('user_id')
                                        ->label('Müşteri')
                                        ->relationship('user', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    Select::make('site_id')
                                        ->label('Site')
                                        ->relationship('site', 'domain')
                                        ->searchable()
                                        ->preload(),
                                    TextInput::make('site_package_id')
                                        ->label('Site paketi ID')
                                        ->numeric()
                                        ->helperText('Paket siparişleri için (ileride).'),
                                    Select::make('content_source')
                                        ->label('İçerik kaynağı')
                                        ->options(ContentSource::class)
                                        ->required()
                                        ->live(),
                                    DatePicker::make('due_date')
                                        ->label('Teslim tarihi')
                                        ->helperText('İçerik kaynağına göre otomatik hesaplanır; gerekirse elle değiştirebilirsiniz.'),
                                    Select::make('status')
                                        ->label('Durum')
                                        ->options(OrderStatus::class)
                                        ->required()
                                        ->default(OrderStatus::PaymentPending)
                                        ->disabled()
                                        ->dehydrated()
                                        ->helperText('Durum yalnızca aksiyonlar ile değiştirilir.'),
                                ]),
                            ]),
                        Tab::make('Fiyat & Editör')
                            ->icon(Heroicon::OutlinedCurrencyDollar)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('price')
                                        ->label('Fiyat')
                                        ->numeric()
                                        ->required()
                                        ->minValue(0)
                                        ->step(0.01),
                                    Select::make('currency')
                                        ->label('Para birimi')
                                        ->options(Currency::class)
                                        ->required()
                                        ->default(Currency::Usd),
                                    Select::make('assigned_editor_id')
                                        ->label('Atanan editör')
                                        ->relationship(
                                            name: 'assignedEditor',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn ($query) => $query->where('role', UserRole::Editor),
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->columnSpanFull(),
                                ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }
}
