<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\ContentSource;
use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Enums\UserRole;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
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
                                    Select::make('product_type')
                                        ->label('Ürün tipi')
                                        ->options(ProductType::class)
                                        ->required()
                                        ->default(ProductType::SiteArticle)
                                        ->live(),
                                    Select::make('site_id')
                                        ->label('Site')
                                        ->relationship('site', 'domain')
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn (Get $get): bool => in_array(self::productType($get), [
                                            ProductType::SiteArticle,
                                            ProductType::PressRelease,
                                            ProductType::FooterLink,
                                        ], true)),
                                    Select::make('instagram_account_id')
                                        ->label('Instagram hesabı')
                                        ->relationship('instagramAccount', 'handle')
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn (Get $get): bool => self::productType($get) === ProductType::Story),
                                    Select::make('site_bundle_id')
                                        ->label('Site paketi')
                                        ->relationship('siteBundle', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn (Get $get): bool => self::productType($get) === ProductType::Bundle),
                                    Select::make('footer_link_duration_option_id')
                                        ->label('Footer link süresi')
                                        ->relationship('footerLinkDurationOption', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn (Get $get): bool => self::productType($get) === ProductType::FooterLink),
                                    Select::make('instagram_story_price_id')
                                        ->label('Story fiyatı')
                                        ->relationship('instagramStoryPrice', 'id')
                                        ->getOptionLabelFromRecordUsing(fn ($record): string => $record->instagramAccount?->handle.' — '.$record->format->getLabel())
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn (Get $get): bool => self::productType($get) === ProductType::Story),
                                    Select::make('seo_package_id')
                                        ->label('SEO paketi')
                                        ->relationship('seoPackage', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn (Get $get): bool => self::productType($get) === ProductType::SeoPackage),
                                    Select::make('seo_package_duration_option_id')
                                        ->label('SEO paketi süresi')
                                        ->relationship('seoPackageDurationOption', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn (Get $get): bool => self::productType($get) === ProductType::SeoPackage),
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

    protected static function productType(Get $get): ?ProductType
    {
        $value = $get('product_type');

        return $value instanceof ProductType ? $value : ProductType::tryFrom((string) $value);
    }
}
