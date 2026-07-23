<?php

namespace App\Filament\Resources\Carts\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CartInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sepet')
                    ->schema([
                        TextEntry::make('id')->label('#'),
                        TextEntry::make('user.name')->label('Kullanıcı')->placeholder('Misafir'),
                        TextEntry::make('status')->label('Durum')->badge(),
                        TextEntry::make('session_token')->label('Oturum')->placeholder('—'),
                        TextEntry::make('updated_at')->label('Güncellenme')->dateTime(),
                    ])
                    ->columns(2),
                Section::make('Kalemler')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('product_type')->label('Ürün')->badge(),
                                TextEntry::make('site.domain')->label('Site')->placeholder('—'),
                                TextEntry::make('siteBundle.name')->label('Paket')->placeholder('—'),
                                TextEntry::make('price')->label('Fiyat')->money(fn ($record) => $record->currency?->value ?? 'TRY'),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }
}
