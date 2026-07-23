<?php

namespace App\Observers;

use App\Models\Page;
use App\Services\CatalogCache;

class PageObserver
{
    public function __construct(private CatalogCache $catalogCache) {}

    public function saved(Page $page): void
    {
        $this->catalogCache->forgetPage($page);
    }

    public function deleted(Page $page): void
    {
        $this->catalogCache->forgetPage($page);
    }
}
