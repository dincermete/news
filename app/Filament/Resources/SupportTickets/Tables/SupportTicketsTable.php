<?php

namespace App\Filament\Resources\SupportTickets\Tables;

use App\Enums\SupportTicketPriority;
use App\Enums\SupportTicketSource;
use App\Enums\SupportTicketStatus;
use App\Enums\UserRole;
use App\Filament\Resources\SupportTickets\Actions\SupportTicketActions;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SupportTicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('subject')->label('Konu')->searchable()->limit(40)->wrap(),
                TextColumn::make('user.name')->label('Müşteri')->placeholder('Misafir')->searchable(),
                TextColumn::make('user.email')->label('E-posta')->toggleable()->searchable()->placeholder('—'),
                TextColumn::make('status')->label('Durum')->badge()->sortable(),
                TextColumn::make('priority')->label('Öncelik')->badge()->sortable(),
                TextColumn::make('assignee.name')->label('Atanan')->placeholder('—')->toggleable(),
                TextColumn::make('source')->label('Kaynak')->badge()->toggleable(),
                TextColumn::make('last_replied_at')->label('Son yanıt')->dateTime('d.m.Y H:i')->sortable()->placeholder('—'),
                TextColumn::make('created_at')->label('Oluşturulma')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Durum')->options(SupportTicketStatus::class),
                SelectFilter::make('priority')->label('Öncelik')->options(SupportTicketPriority::class),
                SelectFilter::make('source')->label('Kaynak')->options(SupportTicketSource::class),
                SelectFilter::make('assigned_to')
                    ->label('Atanan')
                    ->relationship(
                        name: 'assignee',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->whereIn('role', [UserRole::Admin, UserRole::Editor]),
                    )
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('unanswered')
                    ->label('Yanıtsız')
                    ->queries(
                        true: fn ($query) => $query
                            ->where('status', '!=', SupportTicketStatus::Closed)
                            ->whereDoesntHave('messages', fn ($messages) => $messages->where('is_staff', true)),
                        false: fn ($query) => $query,
                        blank: fn ($query) => $query,
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                ...SupportTicketActions::make(),
            ]);
    }
}
