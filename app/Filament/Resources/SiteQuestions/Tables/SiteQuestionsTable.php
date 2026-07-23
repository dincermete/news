<?php

namespace App\Filament\Resources\SiteQuestions\Tables;

use App\Models\SiteQuestion;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SiteQuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('site.domain')->label('Site')->searchable()->sortable(),
                TextColumn::make('question')->label('Soru')->limit(40)->searchable(),
                TextColumn::make('user.name')->label('Kullanıcı')->placeholder('Misafir'),
                TextColumn::make('guest_email')->label('E-posta')->placeholder('—')->toggleable(),
                IconColumn::make('is_public')->label('Açık')->boolean(),
                TextColumn::make('answered_at')->label('Yanıt')->dateTime()->placeholder('Bekliyor')->sortable(),
                TextColumn::make('created_at')->label('Tarih')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                TernaryFilter::make('answered')
                    ->label('Yanıtlandı')
                    ->nullable()
                    ->queries(
                        fn ($query) => $query->whereNotNull('answer'),
                        fn ($query) => $query->whereNull('answer'),
                    ),
                TernaryFilter::make('is_public')->label('Herkese açık'),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('answer')
                    ->label('Yanıtla')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->visible(fn (SiteQuestion $record): bool => blank($record->answer))
                    ->schema([
                        Textarea::make('answer')
                            ->label('Yanıt')
                            ->required()
                            ->rows(5),
                    ])
                    ->action(function (SiteQuestion $record, array $data): void {
                        $record->forceFill([
                            'answer' => $data['answer'],
                            'answered_by' => Auth::id(),
                            'answered_at' => now(),
                        ])->save();

                        Notification::make()
                            ->title('Soru yanıtlandı')
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
