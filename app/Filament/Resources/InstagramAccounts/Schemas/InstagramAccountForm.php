<?php

namespace App\Filament\Resources\InstagramAccounts\Schemas;

use App\Enums\SiteStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class InstagramAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('handle')
                        ->label('Kullanıcı adı')
                        ->prefix('@')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->columnSpan(1),
                    TextInput::make('name')
                        ->label('Görünen ad')
                        ->maxLength(255)
                        ->columnSpan(1),
                    TextInput::make('follower_count')
                        ->label('Takipçi sayısı')
                        ->numeric()
                        ->minValue(0)
                        ->columnSpan(1),
                    Select::make('status')
                        ->label('Durum')
                        ->options(SiteStatus::class)
                        ->required()
                        ->default(SiteStatus::Draft)
                        ->columnSpan(1),
                    FileUpload::make('avatar_url')
                        ->label('Profil fotoğrafı')
                        ->image()
                        ->disk('public')
                        ->directory('instagram-accounts')
                        ->columnSpanFull(),
                ]),
            ]);
    }
}
