<?php

namespace App\Filament\Resources\SpinCreditTransactions\Pages;

use App\Filament\Resources\SpinCreditTransactions\SpinCreditTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListSpinCreditTransactions extends ListRecords
{
    protected static string $resource = SpinCreditTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
