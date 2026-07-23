<?php

namespace App\Filament\Resources\SiteBundles\Schemas;

use App\Enums\Currency;
use App\Enums\SiteStatus;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

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
                                        ->maxLength(255),
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
                                        ->label('Açıklama')
                                        ->rows(4)
                                        ->hintAction(\App\Filament\Actions\AiSuggestFieldAction::make(
                                            'description',
                                            contextField: 'name',
                                        ))
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
