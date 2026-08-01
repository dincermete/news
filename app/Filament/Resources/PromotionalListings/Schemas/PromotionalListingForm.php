<?php

namespace App\Filament\Resources\PromotionalListings\Schemas;

use App\Enums\Currency;
use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Filament\Actions\AiSuggestFieldAction;
use App\Filament\Schemas\StorefrontSeoForm;
use App\Models\PromotionalListing;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class PromotionalListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ürün')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('site_id')
                                ->label('Kaynak site')
                                ->relationship('site', 'domain')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(1),
                            Select::make('type')
                                ->label('Ürün tipi')
                                ->options(PromotionalListingType::class)
                                ->required()
                                ->live()
                                ->unique(
                                    ignoreRecord: true,
                                    modifyRuleUsing: fn (Unique $rule, callable $get): Unique => $rule
                                        ->where('site_id', $get('site_id')),
                                )
                                ->columnSpan(1),
                            Select::make('status')
                                ->label('Satış durumu')
                                ->options(SiteStatus::class)
                                ->required()
                                ->default(SiteStatus::Draft)
                                ->columnSpan(1),
                            Select::make('currency')
                                ->label('Para birimi')
                                ->options(Currency::class)
                                ->required()
                                ->default(Currency::Try)
                                ->columnSpan(1),
                            TextInput::make('price')
                                ->label('Fiyat')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->step(0.01)
                                ->columnSpan(1),
                            TextInput::make('discount_price')
                                ->label('İndirimli fiyat')
                                ->numeric()
                                ->minValue(0)
                                ->step(0.01)
                                ->columnSpan(1),
                            Textarea::make('short_description')
                                ->label('Kısa açıklama')
                                ->rows(2)
                                ->maxLength(500)
                                ->hintAction(AiSuggestFieldAction::make('short_description'))
                                ->columnSpanFull(),
                            RichEditor::make('description')
                                ->label('Ürün açıklaması')
                                ->columnSpanFull(),
                            RichEditor::make('delivery_details')
                                ->label('Teslimat detayları')
                                ->columnSpanFull(),
                            ColorPicker::make('cta_cart_color')
                                ->label('Sepete Ekle rengi')
                                ->default('#111827')
                                ->columnSpan(1),
                            ColorPicker::make('cta_buy_color')
                                ->label('Hemen Satın Al rengi')
                                ->default('#2248ab')
                                ->columnSpan(1),
                            ColorPicker::make('cta_whatsapp_color')
                                ->label('WhatsApp buton rengi')
                                ->default('#25D366')
                                ->columnSpan(1),
                        ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Cross-sell')
                    ->description('Ürün detayının altında yan yana gösterilir. İlgili boşsa aynı kategoriden, tavsiye boşsa çok satanlardan otomatik doldurulur.')
                    ->schema([
                        Select::make('relatedListings')
                            ->label('İlgili ürünler')
                            ->relationship(
                                name: 'relatedListings',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn ($query) => $query
                                    ->with('site')
                                    ->where('promotional_listings.status', SiteStatus::Active)
                                    ->orderBy('promotional_listings.id'),
                                ignoreRecord: true,
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (PromotionalListing $record): string => $record->option_label,
                            )
                            ->searchable(['site.domain'])
                            ->multiple()
                            ->preload()
                            ->columnSpanFull(),
                        Select::make('recommendedListings')
                            ->label('Tavsiye edilen ürünler')
                            ->relationship(
                                name: 'recommendedListings',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn ($query) => $query
                                    ->with('site')
                                    ->where('promotional_listings.status', SiteStatus::Active)
                                    ->orderBy('promotional_listings.id'),
                                ignoreRecord: true,
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (PromotionalListing $record): string => $record->option_label,
                            )
                            ->searchable(['site.domain'])
                            ->multiple()
                            ->preload()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                ...StorefrontSeoForm::section(),
            ]);
    }
}
