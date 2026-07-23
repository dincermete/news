<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountOrderController extends Controller
{
    public function __invoke(Request $request, SeoMetaService $seo): View
    {
        $orders = $request->user()
            ->orders()
            ->with(['site', 'orderGroup'])
            ->latest('id')
            ->paginate(15);

        return view('account.orders', [
            'meta' => $seo->forDefault(),
            'orders' => $orders,
        ]);
    }
}
