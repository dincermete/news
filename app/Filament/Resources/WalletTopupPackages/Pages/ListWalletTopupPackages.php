<?php

namespace App\Filament\Resources\WalletTopupPackages\Pages;

use App\Filament\Resources\WalletTopupPackages\WalletTopupPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWalletTopupPackages extends ListRecords
{
    protected static string $resource = WalletTopupPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
