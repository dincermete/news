<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Fatura no')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.id')
                    ->label('Sipariş')
                    ->formatStateUsing(fn ($state): string => '#'.$state)
                    ->sortable(),
                TextColumn::make('order.user.name')
                    ->label('Müşteri')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('billingProfile.tax_id')
                    ->label('Vergi / TCKN')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                Action::make('downloadPdf')
                    ->label('PDF İndir')
                    ->icon(Heroicon::ArrowDownTray)
                    ->action(function (Invoice $record): StreamedResponse {
                        abort_unless(Storage::disk('local')->exists($record->pdf_path), 404);

                        return Storage::disk('local')->download(
                            $record->pdf_path,
                            $record->invoice_number.'.pdf',
                        );
                    }),
                ViewAction::make(),
            ]);
    }
}
