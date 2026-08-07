<?php

namespace App\Filament\Resources\Carts\Tables;

use App\Enums\CartStatus;
use App\Models\Cart;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->whereHas('items')
                ->with(['user', 'items'])
                ->withCount('items'))
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Müşteri')
                    ->placeholder('Misafir')
                    ->description(fn (Cart $record): ?string => $record->user?->email)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $query) use ($search): void {
                            $query
                                ->whereHas('user', function (Builder $query) use ($search): void {
                                    $query
                                        ->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%");
                                })
                                ->orWhere(function (Builder $query) use ($search): void {
                                    $query
                                        ->whereNull('user_id')
                                        ->where('session_token', 'like', "%{$search}%");
                                });
                        });
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label('Kalem')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('subtotal')
                    ->label('Ara toplam')
                    ->state(fn (Cart $record): float => $record->subtotal())
                    ->money('TRY')
                    ->sortable(false),
                TextColumn::make('updated_at')
                    ->label('Güncellenme')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('session_token')
                    ->label('Oturum')
                    ->limit(12)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(CartStatus::class),
            ])
            ->emptyStateHeading('Kalemi olan sepet yok')
            ->emptyStateDescription('Yalnızca en az bir ürün içeren sepetler listelenir.')
            ->recordActions([ViewAction::make()->label('İncele')])
            ->toolbarActions([]);
    }
}
