<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use App\Services\ApiOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Sepet checkout akışıyla tek site siparişi oluşturur.
     *
     * @bodyParam site_id int required Site ID.
     * @bodyParam billing_profile_id int required Kullanıcıya ait fatura profili.
     * @bodyParam coupon_code string optional Kupon kodu.
     * @bodyParam payment_method string optional card|bank_transfer|balance.
     */
    public function store(Request $request, ApiOrderService $orders): JsonResponse
    {
        $validated = $request->validate([
            'site_id' => ['required', 'integer', 'exists:sites,id'],
            'billing_profile_id' => ['required', 'integer', 'exists:billing_profiles,id'],
            'coupon_code' => ['nullable', 'string', 'max:64'],
            'payment_method' => ['nullable', 'string', Rule::enum(PaymentMethod::class)],
            'content_payload' => ['nullable', 'array'],
        ]);

        $order = $orders->create($request->user(), $validated);

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Kullanıcının kendi sipariş detayını döner.
     */
    public function show(Request $request, Order $order): OrderResource
    {
        abort_unless((int) $order->user_id === (int) $request->user()->getAuthIdentifier(), 403);

        $order->loadMissing(['site', 'publishedLink']);

        return new OrderResource($order);
    }
}
