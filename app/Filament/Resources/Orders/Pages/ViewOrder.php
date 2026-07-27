<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\Actions\OrderStatusActions;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function resolveRecord(int | string $key): Model
    {
        /** @var Order $record */
        $record = parent::resolveRecord($key);

        $record->load([
            'user',
            'site',
            'assignedEditor',
            'siteBundle',
            'footerLinkDurationOption',
            'articleWordPackage',
            'seoPackage',
            'seoPackageDurationOption',
            'backlinkPackage',
            'walletTopupPackage',
            'orderGroup.billingProfile',
            'orderGroup.payments',
            'orderGroup.invoices',
            'orderGroup.orders.site',
            'orderGroup.orders.siteBundle',
            'orderGroup.orders.seoPackage',
            'orderGroup.orders.backlinkPackage',
            'orderGroup.orders.walletTopupPackage',
            'payments',
            'invoice.billingProfile',
            'publishedLinks',
            'contentReviews.editor',
        ]);

        return $record;
    }

    public function getTitle(): string | Htmlable
    {
        return 'Sipariş #'.$this->getRecord()->getKey();
    }

    public function getHeading(): string | Htmlable | null
    {
        return 'Sipariş #'.$this->getRecord()->getKey();
    }

    protected function getHeaderActions(): array
    {
        return [
            ...OrderStatusActions::make(),
            EditAction::make(),
        ];
    }
}
