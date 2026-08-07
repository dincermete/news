<?php

namespace App\Filament\Resources\WalletTopupPackages\Pages;

use App\Filament\Resources\WalletTopupPackages\WalletTopupPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWalletTopupPackages extends ManageRecords
{
    protected static string $resource = WalletTopupPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
