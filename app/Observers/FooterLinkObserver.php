<?php

namespace App\Observers;

use App\Models\FooterLink;
use App\Services\CatalogCache;

class FooterLinkObserver
{
    public function __construct(private CatalogCache $catalogCache) {}

    public function saved(FooterLink $footerLink): void
    {
        $this->catalogCache->forgetFooterLinks();
    }

    public function deleted(FooterLink $footerLink): void
    {
        $this->catalogCache->forgetFooterLinks();
    }
}
