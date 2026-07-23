<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Enums\Currency;
use App\Enums\CustomerStatus;
use App\Filament\Resources\Customers\Actions\CustomerActions;
use App\Models\User;
use App\Models\Wallet;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomersTable
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
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->sortable(),
                TextColumn::make('wallet_balance')
                    ->label('Toplam bakiye')
                    ->state(function (User $record): string {
                        $wallet = $record->wallet ?? Wallet::forUser($record, Currency::Try);

                        return number_format($wallet->totalAvailableBalance(), 2, ',', '.').' ₺';
                    })
                    ->alignEnd(),
                TextColumn::make('total_spent')
                    ->label('Toplam harcama')
                    ->state(fn (User $record): string => number_format($record->totalSpent(), 2, ',', '.').' ₺')
                    ->alignEnd(),
                TextColumn::make('created_at')
                    ->label('Kayıt')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(CustomerStatus::class),
                Filter::make('created_at')
                    ->label('Kayıt tarihi')
                    ->schema([
                        DatePicker::make('from')->label('Başlangıç'),
                        DatePicker::make('until')->label('Bitiş'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date),
                            );
                    }),
                Filter::make('min_total_spent')
                    ->label('Min. harcama')
                    ->schema([
                        TextInput::make('amount')
                            ->label('Minimum toplam harcama')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $amount = $data['amount'] ?? null;

                        if ($amount === null || $amount === '') {
                            return $query;
                        }

                        $min = (float) $amount;

                        return $query->where(function (Builder $outer) use ($min): void {
                            $outer->whereRaw(
                                '(
                                    select coalesce(sum(payments.amount), 0)
                                    from payments
                                    left join orders on orders.id = payments.order_id
                                    left join order_groups on order_groups.id = payments.order_group_id
                                    where payments.status = ?
                                      and (orders.user_id = users.id or order_groups.user_id = users.id)
                                ) >= ?',
                                ['paid', $min],
                            );
                        });
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ...CustomerActions::recordActions(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
