<?php

namespace App\Filament\Resources\SiteBundles\Schemas;

use App\Enums\Currency;
use App\Enums\SiteStatus;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class SiteBundleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('bundleTabs')
                    ->tabs([
                        Tab::make('Genel')
                            ->icon(Heroicon::OutlinedCube)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('name')
                                        ->label('Ad')
                                        ->required()
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state, string $operation): void {
                                            if ($operation !== 'create' || filled($get('slug'))) {
                                                return;
                                            }

                                            $set('slug', Str::slug((string) $state));
                                        }),
                                    TextInput::make('slug')
                                        ->label('Slug / URL yolu')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255)
                                        ->helperText('Sayfa URL\'si: /tanitim-paketleri/{slug}. SSS eklerken FaqEntry kategorisini "bundle-{slug}" olarak girin.'),
                                    Select::make('status')
                                        ->label('Durum')
                                        ->options(SiteStatus::class)
                                        ->required()
                                        ->default(SiteStatus::Draft),
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
                                        ->default(Currency::Try),
                                    Textarea::make('description')
                                        ->label('Kısa açıklama')
                                        ->rows(4)
                                        ->hintAction(\App\Filament\Actions\AiSuggestFieldAction::make(
                                            'description',
                                            contextField: 'name',
                                        ))
                                        ->columnSpanFull(),
                                    RichEditor::make('content')
                                        ->label('Paket detay yazısı')
                                        ->helperText('Paket detay sayfasında "Paket Hakkında" bölümünde gösterilir.')
                                        ->columnSpanFull(),
                                ]),
                            ]),
                        Tab::make('Siteler')
                            ->icon(Heroicon::OutlinedGlobeAlt)
                            ->schema([
                                CheckboxList::make('sites')
                                    ->label('Paket siteleri')
                                    ->relationship('sites', 'domain')
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }
}
