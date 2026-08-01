<?php

namespace App\Filament\Resources\SiteReviews\Tables;

use App\Filament\Actions\BulkActionGroup;
use App\Models\SiteReview;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SiteReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('site.domain')->label('Site')->searchable()->sortable(),
                TextColumn::make('name')->label('Ad soyad')->searchable(),
                TextColumn::make('email')->label('E-posta')->toggleable(),
                TextColumn::make('phone')->label('Telefon')->toggleable(),
                TextColumn::make('message')->label('Mesaj')->limit(40)->searchable(),
                IconColumn::make('is_approved')->label('Onaylı')->boolean(),
                TextColumn::make('approved_at')->label('Onay')->dateTime()->placeholder('Bekliyor')->sortable(),
                TextColumn::make('created_at')->label('Tarih')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                TernaryFilter::make('is_approved')->label('Onaylı'),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('approve')
                    ->label('Onayla')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (SiteReview $record): bool => ! $record->is_approved)
                    ->requiresConfirmation()
                    ->action(function (SiteReview $record): void {
                        $record->forceFill([
                            'is_approved' => true,
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ])->save();

                        Notification::make()
                            ->title('Yorum onaylandı')
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
