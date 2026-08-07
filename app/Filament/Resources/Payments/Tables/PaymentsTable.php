<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Actions\BulkActionGroup;
use App\Models\Payment;
use App\Services\PaymentCompletionService;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['order.user', 'orderGroup.user', 'orderGroup.orders']))
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('order.id')
                    ->label('Sipariş')
                    ->formatStateUsing(fn ($state): string => filled($state) ? '#'.$state : '—')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('order_group_id')
                    ->label('Sepet grubu')
                    ->formatStateUsing(fn ($state): string => filled($state) ? '#'.$state : '—')
                    ->toggleable(),
                TextColumn::make('order.user.name')
                    ->label('Müşteri')
                    ->searchable()
                    ->sortable()
                    ->placeholder(fn (Payment $record): string => $record->orderGroup?->user?->name ?? '—'),
                TextColumn::make('amount')
                    ->label('Tutar')
                    ->money(fn (Payment $record): string => $record->currency?->value ?? 'TRY')
                    ->sortable(),
                TextColumn::make('method')
                    ->label('Yöntem')
                    ->badge()
                    ->sortable(),
                TextColumn::make('reference_code')
                    ->label('Referans')
                    ->searchable()
                    ->fontFamily('mono')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Ödeme zamanı')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('receipt_path')
                    ->label('Dekont')
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Var' : 'Yok')
                    ->badge()
                    ->color(fn (?string $state): string => filled($state) ? 'success' : 'gray')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Filter::make('pending_bank_transfers')
                    ->label('Havale Onayı Bekleyenler')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('method', PaymentMethod::BankTransfer)
                        ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Notified]))
                    ->toggle(),
                SelectFilter::make('method')
                    ->label('Yöntem')
                    ->options(PaymentMethod::class),
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(PaymentStatus::class),
            ])
            ->recordActions([
                Action::make('approveBankTransfer')
                    ->label('Havale Onayla')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Havale onayla')
                    ->modalDescription('Ödeme Paid yapılacak; siparişler ilerletilecek. Bakiye paketlerinde cüzdan anında yüklenecek. Müşteri bildirim vermemişse (Pending) yine de onaylayabilirsiniz.')
                    ->visible(fn (Payment $record): bool => $record->isPendingBankTransfer())
                    ->action(function (Payment $record): void {
                        app(PaymentCompletionService::class)->complete($record);

                        $fresh = $record->fresh(['orderGroup.orders', 'order']);
                        $topupTotal = $fresh?->walletTopupOrders()->sum(fn ($order) => (float) $order->price) ?? 0;

                        Notification::make()
                            ->title('Havale onaylandı')
                            ->body($topupTotal > 0
                                ? 'Ödeme tamamlandı. Cüzdana '.number_format($topupTotal, 2, ',', '.').' ₺ yüklendi.'
                                : 'Ödeme ve sipariş güncellendi.')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
