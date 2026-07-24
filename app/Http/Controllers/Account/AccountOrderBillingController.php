<?php

namespace App\Http\Controllers\Account;

use App\Enums\BillingProfileType;
use App\Http\Controllers\Controller;
use App\Jobs\InvoiceGenerationJob;
use App\Models\Invoice;
use App\Models\OrderGroup;
use App\Services\BillingProfileResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AccountOrderBillingController extends Controller
{
    public function __construct(
        protected BillingProfileResolver $billingProfiles,
    ) {}

    public function __invoke(Request $request, OrderGroup $orderGroup): RedirectResponse
    {
        abort_unless((int) $orderGroup->user_id === (int) $request->user()->id, 403);

        $data = $request->validate([
            'billing_profile_id' => [
                'nullable',
                'integer',
                Rule::exists('billing_profiles', 'id')->where('user_id', $request->user()->id),
            ],
            'billing_type' => ['required_without:billing_profile_id', Rule::enum(BillingProfileType::class)],
            'tax_id' => ['required_without:billing_profile_id', 'string', 'max:32'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['required_without:billing_profile_id', 'string', 'max:2000'],
            'tax_office' => ['nullable', 'string', 'max:255'],
        ]);

        $billingProfile = $this->billingProfiles->resolveRequired($request, $data);

        $orderGroup->forceFill(['billing_profile_id' => $billingProfile->id])->save();

        $this->regenerateInvoiceIfExists($orderGroup);

        return redirect()
            ->route('account.orders.show', $orderGroup)
            ->with('status', 'Fatura bilgileriniz kaydedildi.');
    }

    protected function regenerateInvoiceIfExists(OrderGroup $orderGroup): void
    {
        $invoice = Invoice::query()->where('order_group_id', $orderGroup->id)->first();

        if ($invoice === null) {
            return;
        }

        if (filled($invoice->pdf_path)) {
            Storage::disk('local')->delete($invoice->pdf_path);
        }

        $invoice->delete();

        $payment = $orderGroup->payments()->latest('id')->first();

        if ($payment !== null) {
            InvoiceGenerationJob::dispatchSync($payment);
        }
    }
}
