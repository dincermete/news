<?php

namespace App\Filament\Resources\Sites\Pages;

use App\Filament\Resources\Sites\SiteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSite extends CreateRecord
{
    protected static string $resource = SiteResource::class;

    public function mount(): void
    {
        parent::mount();

        $fill = array_filter([
            'domain' => request()->query('domain'),
            'price' => request()->query('price'),
            'site_category_id' => request()->query('site_category_id'),
            'age' => request()->query('age'),
        ], fn (mixed $value): bool => filled($value));

        if ($fill !== []) {
            $this->form->fill($fill);
        }
    }
}
