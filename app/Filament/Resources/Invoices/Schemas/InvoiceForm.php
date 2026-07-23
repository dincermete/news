<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->label('Sipariş')
                    ->relationship('order', 'id')
                    ->disabled(),
                TextInput::make('invoice_number')
                    ->label('Fatura no')
                    ->disabled(),
                TextInput::make('pdf_path')
                    ->label('PDF yolu')
                    ->disabled(),
                Select::make('billing_profile_id')
                    ->label('Fatura profili')
                    ->relationship('billingProfile', 'tax_id')
                    ->disabled(),
            ])
            ->disabled();
    }
}
