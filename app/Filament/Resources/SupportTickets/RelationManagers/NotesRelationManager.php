<?php

namespace App\Filament\Resources\SupportTickets\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $title = 'İç notlar';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('admin.name')->label('Admin')->placeholder('—'),
                TextColumn::make('body')->label('Not')->wrap()->limit(160),
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Not ekle')
                    ->schema([
                        Textarea::make('body')
                            ->label('Not')
                            ->required()
                            ->rows(4),
                    ])
                    ->mutateDataUsing(function (array $data): array {
                        $data['admin_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
