<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BillingProfile;
use App\Models\Invoice;
use App\Models\OrderGroup;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountOrderController extends Controller
{
    public function __invoke(Request $request, SeoMetaService $seo): View
    {
        $orders = $request->user()
            ->orders()
            ->with(['site', 'instagramAccount', 'seoPackage', 'backlinkPackage', 'orderGroup'])
            ->latest('id')
            ->paginate(15);

        return view('account.orders', [
            'meta' => $seo->forDefault(),
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, SeoMetaService $seo, OrderGroup $orderGroup): View
    {
        abort_unless((int) $orderGroup->user_id === (int) $request->user()->id, 403);

        $orderGroup->load([
            'orders.site',
            'orders.siteBundle',
            'orders.footerLinkDurationOption',
            'orders.instagramAccount',
            'orders.instagramStoryPrice',
            'orders.seoPackage',
            'orders.seoPackageDurationOption',
            'orders.backlinkPackage',
            'orders.publishedLink',
            'payments',
            'billingProfile',
        ]);

        $invoice = Invoice::query()->where('order_group_id', $orderGroup->id)->first();

        $billingProfiles = BillingProfile::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->get();

        return view('account.order-show', [
            'meta' => $seo->forDefault(),
            'orderGroup' => $orderGroup,
            'invoice' => $invoice,
            'billingProfiles' => $billingProfiles,
        ]);
    }
}
