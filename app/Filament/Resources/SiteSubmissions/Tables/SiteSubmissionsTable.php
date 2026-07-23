<?php

namespace App\Filament\Resources\SiteSubmissions\Tables;

use App\Enums\SiteSubmissionStatus;
use App\Filament\Resources\Sites\SiteResource;
use App\Models\SiteSubmission;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SiteSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Kullanıcı')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('url')
                    ->label('URL')
                    ->searchable()
                    ->limit(40)
                    ->url(fn (SiteSubmission $record): string => $record->url, shouldOpenInNewTab: true),
                TextColumn::make('price')
                    ->label('Fiyat')
                    ->money('TRY')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Başvuru tarihi')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(SiteSubmissionStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('approve')
                    ->label('Onayla')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->visible(fn (SiteSubmission $record): bool => $record->status === SiteSubmissionStatus::Pending)
                    ->schema([
                        Textarea::make('admin_note')
                            ->label('Not (opsiyonel)')
                            ->rows(3),
                    ])
                    ->action(function (SiteSubmission $record, array $data): void {
                        $record->forceFill([
                            'status' => SiteSubmissionStatus::Approved,
                            'admin_note' => $data['admin_note'] ?? null,
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ])->save();

                        Notification::make()
                            ->title('Başvuru onaylandı')
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Reddet')
                    ->icon(Heroicon::XCircle)
                    ->color('danger')
                    ->visible(fn (SiteSubmission $record): bool => $record->status === SiteSubmissionStatus::Pending)
                    ->schema([
                        Textarea::make('admin_note')
                            ->label('Red sebebi')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (SiteSubmission $record, array $data): void {
                        $record->forceFill([
                            'status' => SiteSubmissionStatus::Rejected,
                            'admin_note' => $data['admin_note'],
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ])->save();

                        Notification::make()
                            ->title('Başvuru reddedildi')
                            ->success()
                            ->send();
                    }),
                Action::make('convertToSite')
                    ->label('Siteye Dönüştür')
                    ->icon(Heroicon::ArrowTopRightOnSquare)
                    ->color('primary')
                    ->visible(fn (SiteSubmission $record): bool => $record->status === SiteSubmissionStatus::Approved)
                    ->url(fn (SiteSubmission $record): string => SiteResource::getUrl('create').'?'.http_build_query($record->siteCreateQuery()))
                    ->openUrlInNewTab(),
            ]);
    }
}
