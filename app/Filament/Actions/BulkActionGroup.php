<?php

namespace App\Filament\Actions;

use Filament\Actions\BulkActionGroup as BaseBulkActionGroup;

/**
 * Single labeled bulk-actions trigger (no duplicate icon + label pair).
 */
class BulkActionGroup extends BaseBulkActionGroup
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->labeledFrom(null);
    }
}
