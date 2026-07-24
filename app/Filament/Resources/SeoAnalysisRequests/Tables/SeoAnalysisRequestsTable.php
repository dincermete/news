<?php

namespace App\Filament\Resources\SeoAnalysisRequests\Tables;

use App\Enums\SeoAnalysisStatus;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SeoAnalysisRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Müşteri')->searchable()->sortable(),
                TextColumn::make('site_url')->label('Site adresi')->searchable()->limit(40),
                TextColumn::make('service_type')->label('Hizmet')->badge()->sortable(),
                TextColumn::make('status')->label('Durum')->badge()->sortable(),
                TextColumn::make('created_at')->label('Talep tarihi')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Durum')->options(SeoAnalysisStatus::class),
            ])
            ->recordActions([EditAction::make()]);
    }
}
