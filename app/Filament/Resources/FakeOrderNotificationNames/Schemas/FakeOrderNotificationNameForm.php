<?php

namespace App\Filament\Resources\FakeOrderNotificationNames\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FakeOrderNotificationNameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('İsim')
                    ->required()
                    ->maxLength(100),
                TextInput::make('city')
                    ->label('Şehir')
                    ->required()
                    ->maxLength(100),
            ]);
    }
}
