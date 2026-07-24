<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Jobs\ProcessSuccessfulPayment;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                        ->where('status', PaymentStatus::Notified))
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
                    ->visible(fn (Payment $record): bool => $record->isPendingBankTransfer())
                    ->action(function (Payment $record): void {
                        DB::transaction(function () use ($record): void {
                            $record->forceFill([
                                'status' => PaymentStatus::Paid,
                                'paid_at' => now(),
                            ])->save();

                            $record->loadMissing(['order', 'orderGroup.orders']);
                            $record->markRelatedOrdersContentPending();
                        });

                        ProcessSuccessfulPayment::dispatch($record->fresh());

                        Notification::make()
                            ->title('Havale onaylandı')
                            ->body('Ödeme ve sipariş güncellendi, fatura kuyruğa alındı.')
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
