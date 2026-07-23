<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('PDF İndir')
                ->icon(Heroicon::ArrowDownTray)
                ->action(function (): StreamedResponse {
                    $record = $this->getRecord();

                    abort_unless(Storage::disk('local')->exists($record->pdf_path), 404);

                    return Storage::disk('local')->download(
                        $record->pdf_path,
                        $record->invoice_number.'.pdf',
                    );
                }),
        ];
    }
}
