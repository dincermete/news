<?php

namespace App\Filament\Resources\Carts\Pages;

use App\Filament\Resources\Carts\CartResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewCart extends ViewRecord
{
    protected static string $resource = CartResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->getRecord()->loadMissing([
            'user',
            'items.site',
            'items.siteBundle',
            'items.seoPackage',
            'items.backlinkPackage',
            'items.walletTopupPackage',
            'items.footerLinkDurationOption',
            'items.seoPackageDurationOption',
            'items.articleWordPackage',
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Sepet #'.$this->getRecord()->getKey();
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Sepet #'.$this->getRecord()->getKey();
    }
}
