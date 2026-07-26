<?php

namespace App\Filament\Resources\SupportTickets\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Konuşma';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_staff')
                    ->label('Personel')
                    ->boolean(),
                TextColumn::make('user.name')
                    ->label('Yazar')
                    ->placeholder('Sistem'),
                TextColumn::make('body')
                    ->label('Mesaj')
                    ->wrap()
                    ->limit(200),
                TextColumn::make('created_at')
                    ->label('Zaman')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at')
            ->paginated(false);
    }
}
