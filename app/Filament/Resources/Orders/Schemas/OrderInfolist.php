<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.orders.view')
                    ->columnSpanFull(),
            ]);
    }
}
