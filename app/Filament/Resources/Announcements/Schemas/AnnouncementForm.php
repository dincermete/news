<?php

namespace App\Filament\Resources\Announcements\Schemas;

use App\Enums\NotificationAudience;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Başlık')
                    ->required()
                    ->maxLength(255),
                Textarea::make('body')
                    ->label('İçerik')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                Select::make('audience')
                    ->label('Hedef kitle')
                    ->options(NotificationAudience::class)
                    ->required()
                    ->default(NotificationAudience::All),
                DateTimePicker::make('starts_at')
                    ->label('Başlangıç')
                    ->seconds(false),
                DateTimePicker::make('ends_at')
                    ->label('Bitiş')
                    ->seconds(false),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
