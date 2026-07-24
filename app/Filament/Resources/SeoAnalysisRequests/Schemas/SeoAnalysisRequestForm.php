<?php

namespace App\Filament\Resources\SeoAnalysisRequests\Schemas;

use App\Enums\SeoAnalysisStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class SeoAnalysisRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Select::make('user_id')
                        ->label('Müşteri')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled()
                        ->dehydrated(),
                    TextInput::make('site_url')
                        ->label('Site adresi')
                        ->url()
                        ->required()
                        ->maxLength(2048)
                        ->disabled()
                        ->dehydrated(),
                    Select::make('service_type')
                        ->label('Hizmet')
                        ->options(\App\Enums\SeoAnalysisServiceType::class)
                        ->required()
                        ->disabled()
                        ->dehydrated(),
                    Select::make('status')
                        ->label('Durum')
                        ->options(SeoAnalysisStatus::class)
                        ->required()
                        ->default(SeoAnalysisStatus::Pending),
                    Textarea::make('brief')
                        ->label('Müşteri notu')
                        ->rows(3)
                        ->disabled()
                        ->dehydrated()
                        ->columnSpanFull(),
                    Textarea::make('result')
                        ->label('Analiz sonucu')
                        ->rows(6)
                        ->helperText('Buraya yazdığınız sonuç, durum "Tamamlandı" olduğunda müşterinin hesabım panelinde görünür.')
                        ->columnSpanFull(),
                ]),
            ]);
    }
}
