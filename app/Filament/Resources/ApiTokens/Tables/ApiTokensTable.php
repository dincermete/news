<?php

namespace App\Filament\Resources\ApiTokens\Tables;

use App\Enums\ApiTokenAbility;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ad')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tokenable.name')
                    ->label('Kullanıcı')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('abilities')
                    ->label('Yetkiler')
                    ->formatStateUsing(function ($state): string {
                        $abilities = is_array($state) ? $state : [];

                        return collect($abilities)
                            ->map(fn (string $ability): string => ApiTokenAbility::tryFrom($ability)?->getLabel() ?? $ability)
                            ->implode(', ');
                    })
                    ->wrap(),
                TextColumn::make('last_used_at')
                    ->label('Son kullanım')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('revoke')
                    ->label('İptal et')
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('API anahtarını iptal et')
                    ->modalDescription('Bu anahtar kalıcı olarak silinir ve artık kullanılamaz.')
                    ->action(function (PersonalAccessToken $record): void {
                        $record->delete();

                        Notification::make()
                            ->title('API anahtarı iptal edildi')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    ->label('Sil'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
