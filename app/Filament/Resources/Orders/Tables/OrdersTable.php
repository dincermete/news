<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\ContentSource;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Orders\Actions\OrderStatusActions;
use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Müşteri')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('site.domain')
                    ->label('Site')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Teslim tarihi')
                    ->date()
                    ->sortable(),
                TextColumn::make('content_source')
                    ->label('İçerik kaynağı')
                    ->badge()
                    ->sortable(),
                TextColumn::make('assignedEditor.name')
                    ->label('Editör')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('price')
                    ->label('Fiyat')
                    ->money(fn (Order $record): string => $record->currency?->value ?? 'USD')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(OrderStatus::class),
                SelectFilter::make('content_source')
                    ->label('İçerik kaynağı')
                    ->options(ContentSource::class),
                SelectFilter::make('assigned_editor_id')
                    ->label('Editör')
                    ->relationship(
                        name: 'assignedEditor',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->where('role', UserRole::Editor),
                    )
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label('Müşteri')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'domain')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                ...OrderStatusActions::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
