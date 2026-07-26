<?php

namespace App\Filament\Resources\PanelUsers\Tables;

use App\Enums\CustomerStatus;
use App\Enums\UserRole;
use App\Filament\Resources\PanelUsers\PanelUserResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PanelUsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ad')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Rol / yetki')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('email_verified_at')
                    ->label('E-posta doğrulama')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('Doğrulanmadı')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Rol')
                    ->options([
                        UserRole::Admin->value => UserRole::Admin->getLabel(),
                        UserRole::Editor->value => UserRole::Editor->getLabel(),
                    ]),
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(CustomerStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn ($record): bool => PanelUserResource::canDelete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->using(function ($records): void {
                            $authId = (int) auth()->id();

                            foreach ($records as $record) {
                                if ((int) $record->getKey() === $authId) {
                                    continue;
                                }

                                $record->delete();
                            }
                        }),
                ]),
            ])
            ->defaultSort('name');
    }
}
