<?php

namespace App\Filament\Resources\FakeOrderNotificationTemplates\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FakeOrderNotificationTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('message_template')
                    ->label('Mesaj şablonu')
                    ->helperText('Yer tutucular: {isim}, {sehir}, {urun}')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('display_interval_seconds')
                    ->label('Gösterim aralığı (sn)')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->default(30),
                TextInput::make('sort_order')
                    ->label('Sıra')
                    ->numeric()
                    ->integer()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
