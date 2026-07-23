<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountInvoiceController extends Controller
{
    public function index(Request $request, SeoMetaService $seo): View
    {
        $user = $request->user();

        $invoices = Invoice::query()
            ->with(['order.site', 'orderGroup', 'billingProfile'])
            ->where(function ($query) use ($user): void {
                $query->whereHas('order', fn ($order) => $order->where('user_id', $user->id))
                    ->orWhereHas('orderGroup', fn ($group) => $group->where('user_id', $user->id));
            })
            ->latest('id')
            ->paginate(15);

        return view('account.invoices', [
            'meta' => $seo->forDefault(),
            'invoices' => $invoices,
        ]);
    }

    public function download(Request $request, Invoice $invoice): StreamedResponse
    {
        $user = $request->user();
        $invoice->loadMissing(['order', 'orderGroup']);

        $owns = ($invoice->order && (int) $invoice->order->user_id === (int) $user->id)
            || ($invoice->orderGroup && (int) $invoice->orderGroup->user_id === (int) $user->id);

        abort_unless($owns, 403);
        abort_unless(filled($invoice->pdf_path) && Storage::disk('local')->exists($invoice->pdf_path), 404);

        return Storage::disk('local')->download(
            $invoice->pdf_path,
            $invoice->invoice_number.'.pdf',
        );
    }
}
