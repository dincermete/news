<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Models\Payment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Ödeme Geçmişi';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $ownerId = $this->getOwnerRecord()->getKey();

                return Payment::query()
                    ->where(function (Builder $q) use ($ownerId): void {
                        $q->whereHas('order', fn (Builder $order) => $order->where('user_id', $ownerId))
                            ->orWhereHas('orderGroup', fn (Builder $group) => $group->where('user_id', $ownerId));
                    });
            })
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('amount')->label('Tutar')->numeric(decimalPlaces: 2),
                TextColumn::make('currency')->label('Para birimi')->badge(),
                TextColumn::make('method')->label('Yöntem')->badge(),
                TextColumn::make('status')->label('Durum')->badge(),
                TextColumn::make('paid_at')->label('Ödeme')->dateTime('d.m.Y H:i')->placeholder('—'),
                TextColumn::make('created_at')->label('Oluşturma')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
