@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-14 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-[20px] border border-emerald-200 bg-emerald-50 px-5 py-3.5 text-sm font-medium text-emerald-800" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-[20px] border border-amber-200 bg-amber-50 px-5 py-3.5 text-sm text-amber-900" role="alert">
                <ul class="list-disc space-y-0.5 ps-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                @if (session('wallet_topup'))
                    <p class="mt-2">
                        <a href="{{ route('account.payment-notification') }}" class="font-semibold text-amber-900 underline underline-offset-2">Bakiye Yükle</a>
                    </p>
                @endif
            </div>
        @endif

        <div class="rounded-[20px] border border-ink/10 bg-paper p-8 text-center sm:p-10">
            <span class="mx-auto inline-flex size-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <svg class="size-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            </span>

            <h1 class="mt-5 font-display text-2xl font-medium text-ink sm:text-[28px]">Siparişiniz Alındı</h1>
            <p class="mt-2 text-sm text-ink-2">Sipariş grubu #{{ $orderGroup->id }} başarıyla oluşturuldu.</p>

            <dl class="mx-auto mt-7 max-w-sm space-y-2.5 rounded-2xl border border-ink/10 bg-white p-5 text-start text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-ink-2">Ara toplam</dt>
                    <dd class="font-semibold text-ink">{{ number_format((float) $orderGroup->subtotal, 2, ',', '.') }} ₺</dd>
                </div>
                @if ((float) $orderGroup->discount_tier_amount > 0)
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-2">Kademe indirimi</dt>
                        <dd class="font-semibold text-emerald-600">−{{ number_format((float) $orderGroup->discount_tier_amount, 2, ',', '.') }} ₺</dd>
                    </div>
                @endif
                @if ((float) $orderGroup->coupon_discount_amount > 0)
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-2">Kupon</dt>
                        <dd class="font-semibold text-emerald-600">−{{ number_format((float) $orderGroup->coupon_discount_amount, 2, ',', '.') }} ₺</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-3 border-t border-ink/10 pt-2.5">
                    <dt class="font-display font-semibold text-ink">Toplam</dt>
                    <dd class="font-display font-bold text-ink">{{ number_format((float) $orderGroup->total, 2, ',', '.') }} ₺</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-ink-2">Kalem</dt>
                    <dd class="font-semibold text-ink">{{ $orderGroup->orders->count() }} sipariş</dd>
                </div>
            </dl>

            <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('account.orders') }}" class="group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 text-sm font-medium text-white transition hover:scale-[1.02] active:scale-[0.98]">
                    <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                        <svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </span>
                    Siparişlerim
                </a>
                <a href="{{ route('sites.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-ink/10 bg-white px-5 py-3 text-sm font-semibold text-ink transition hover:border-ink/25">
                    Alışverişe devam
                </a>
            </div>
        </div>
    </div>
@endsection
