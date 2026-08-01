<?php

namespace App\Filament\Resources\SiteReviews\Pages;

use App\Filament\Resources\SiteReviews\SiteReviewResource;
use App\Models\SiteReview;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSiteReview extends EditRecord
{
    protected static string $resource = SiteReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var SiteReview $record */
        $record = $this->getRecord();

        if (($data['is_approved'] ?? false) && blank($record->approved_at)) {
            $data['approved_by'] = Auth::id();
            $data['approved_at'] = now();
        }

        if (! ($data['is_approved'] ?? false)) {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }

        return $data;
    }
}
