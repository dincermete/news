<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Models\SupportTicket;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SupportTicketsRelationManager extends RelationManager
{
    protected static string $relationship = 'supportTickets';

    protected static ?string $title = 'Destek Talepleri';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('subject')->label('Konu')->limit(40),
                TextColumn::make('status')->label('Durum')->badge(),
                TextColumn::make('priority')->label('Öncelik')->badge(),
                TextColumn::make('source')->label('Kaynak')->badge(),
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (SupportTicket $record): string => SupportTicketResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ViewAction::make()
                    ->url(fn (SupportTicket $record): string => SupportTicketResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
