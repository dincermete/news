<?php

namespace App\Filament\Resources\WalletTopupPackages\Pages;

use App\Filament\Resources\WalletTopupPackages\WalletTopupPackageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWalletTopupPackage extends EditRecord
{
    protected static string $resource = WalletTopupPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
