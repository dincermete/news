<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerNotesRelationManager extends RelationManager
{
    protected static string $relationship = 'customerNotes';

    protected static ?string $title = 'Notlar';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('admin.name')->label('Admin'),
                TextColumn::make('body')->label('Not')->wrap()->limit(120),
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
