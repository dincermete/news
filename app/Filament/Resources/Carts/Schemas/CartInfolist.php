<?php

namespace App\Filament\Resources\Carts\Schemas;

use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class CartInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.carts.view')
                    ->columnSpanFull(),
            ]);
    }
}
