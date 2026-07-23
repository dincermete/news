<?php

namespace App\Filament\Resources\SupportTickets\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatbotMessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'chatbotMessages';

    protected static ?string $title = 'Chatbot mesajları';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('role')->label('Rol')->badge(),
                TextColumn::make('content')->label('Mesaj')->wrap()->limit(120),
                TextColumn::make('created_at')->label('Zaman')->dateTime(),
            ])
            ->defaultSort('id')
            ->paginated(false);
    }

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->chatbot_conversation_id !== null;
    }
}
