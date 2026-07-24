@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    use App\Enums\PaymentMethod;
    use Illuminate\Support\Js;

    $btnDark = 'group inline-flex w-full items-center justify-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 py-3 text-sm font-semibold text-white transition hover:scale-[1.01] active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100';
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto px-5 py-9 sm:px-8" data-reveal>
                <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80">
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Son adım</span>
                    Güvenli ödeme
                </p>
                <h1 class="mt-4 font-display text-3xl font-medium leading-tight sm:text-4xl">Ödeme</h1>
                <p class="mt-1.5 text-sm text-white/60">Ödeme yöntemini seçin.</p>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        @if ($errors->any())
            <div class="mb-6 rounded-[20px] border border-brand-200 bg-brand-50 px-5 py-3.5 text-sm text-brand-800" role="alert">
                <ul class="list-disc space-y-0.5 ps-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                @if (session('wallet_topup'))
                    <p class="mt-2">
                        <a href="{{ route('account.payment-notification') }}" class="font-semibold text-brand-900 underline underline-offset-2">Bakiye Yükle</a>
                    </p>
                @endif
            </div>
        @endif

        @if (session('status'))
            <div class="mb-6 rounded-[20px] border border-emerald-200 bg-emerald-50 px-5 py-3.5 text-sm font-medium text-emerald-800" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @php
            $initialTab = $postSubmitMethod?->value ?? old('payment_method', 'card');
            if ($hasWalletTopupItem && $initialTab === 'balance') {
                $initialTab = 'card';
            }
        @endphp
        <div
            class="grid gap-6 lg:grid-cols-3"
            x-data="{
                tab: '{{ $initialTab }}',
                locked: {{ $postSubmitMethod ? 'true' : 'false' }},
                payable: {{ Js::from($payable) }}
            }"
        >
            <div class="space-y-5 lg:col-span-2">
                @unless ($postSubmitMethod)
                    <form
                        method="post"
                        action="{{ route('checkout.process') }}"
                        x-data="{
                            contracts: {{ old('contracts_accepted') ? 'true' : 'false' }},
                        }"
                        class="space-y-5"
                    >
                        @csrf
                        <input type="hidden" name="payment_method" :value="tab">

                        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                            <h2 class="font-display text-base font-semibold text-ink">Ödeme Yöntemi</h2>
                            <div class="mt-3">
                                @include('checkout.partials.method-tiles')
                            </div>

                            <div class="mt-4">
                                <div x-show="tab === 'card'" x-cloak>
                                    @include('checkout.partials.card-tile')
                                </div>
                                <div x-show="tab === 'bank_transfer'" x-cloak>
                                    @include('checkout.partials.bank-transfer-tile')
                                </div>
                                <div x-show="tab === 'balance'" x-cloak>
                                    @include('checkout.partials.balance-tile')
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                            <label class="flex items-start gap-x-2.5 text-sm text-ink-2">
                                <input type="checkbox" name="contracts_accepted" value="1" class="mt-0.5 size-4 rounded border-ink/20 text-ink focus:ring-0" x-model="contracts">
                                <span>
                                    <a href="{{ route('pages.show', 'mesafeli-satis-sozlesmesi') }}" target="_blank" class="font-semibold text-accent-700 hover:text-accent-800">Mesafeli satış sözleşmesi</a>
                                    ve
                                    <a href="{{ route('pages.show', 'on-bilgilendirme-formu') }}" target="_blank" class="font-semibold text-accent-700 hover:text-accent-800">ön bilgilendirme formu</a>
                                    nu okudum, onaylıyorum.
                                </span>
                            </label>
                        </div>
                    </form>
                @else
                    <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                        <h2 class="font-display text-base font-semibold text-ink">Ödeme Yöntemi</h2>
                        <div class="mt-3">
                            @include('checkout.partials.method-tiles')
                        </div>

                        <div class="mt-4">
                            @if ($postSubmitMethod === PaymentMethod::Card)
                                @include('checkout.partials.card-tile')
                            @elseif ($postSubmitMethod === PaymentMethod::BankTransfer)
                                @include('checkout.partials.bank-transfer-tile')
                            @endif
                        </div>
                    </div>
                @endunless
            </div>

            <aside class="lg:col-span-1">
                <div class="rounded-[20px] border border-ink/10 bg-paper p-5 lg:sticky lg:top-28">
                    <h2 class="font-display text-base font-semibold text-ink">Sipariş Özeti</h2>
                    <p class="mt-0.5 text-xs text-ink-3">{{ $lineItems->count() }} ürün</p>

                    <div class="mt-4 max-h-72 space-y-3 overflow-y-auto pe-1">
                        @foreach ($lineItems as $item)
                            @php
                                $itemTitle = $item->site?->domain
                                    ?? $item->siteBundle?->name
                                    ?? $item->instagramAccount?->handle
                                    ?? $item->seoPackage?->name
                                    ?? $item->backlinkPackage?->name
                                    ?? $item->product_type?->getLabel()
                                    ?? 'Ürün #'.$item->id;
                            @endphp
                            <div class="flex items-start justify-between gap-3 border-b border-ink/5 pb-3 last:border-0 last:pb-0">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if ($item->site)
                                            <x-site-favicon :domain="$item->site->domain" :size="20" class="shrink-0 rounded-md" />
                                        @elseif ($item->instagramAccount)
                                            <x-instagram-avatar :account="$item->instagramAccount" :size="20" />
                                        @endif
                                        <p class="truncate text-sm font-medium text-ink">{{ $itemTitle }}</p>
                                    </div>
                                    <div class="mt-1">
                                        @include('partials.cart-item-badge', ['item' => $item])
                                    </div>
                                </div>
                                <span class="shrink-0 font-display text-sm font-bold text-ink">{{ number_format((float) $item->price, 2, ',', '.') }} ₺</span>
                            </div>
                        @endforeach
                    </div>

                    <dl class="mt-4 space-y-2.5 border-t border-ink/10 pt-4 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-2">Ara toplam</dt>
                            <dd class="font-semibold text-ink">{{ number_format($summary['subtotal'], 2, ',', '.') }} ₺</dd>
                        </div>
                        @if ($summary['tier_discount'] > 0)
                            <div class="flex justify-between gap-3">
                                <dt class="text-ink-2">Kademe indirimi</dt>
                                <dd class="font-semibold text-emerald-600">−{{ number_format($summary['tier_discount'], 2, ',', '.') }} ₺</dd>
                            </div>
                        @endif
                        @if ($summary['coupon_discount'] > 0)
                            <div class="flex justify-between gap-3">
                                <dt class="text-ink-2">Kupon indirimi</dt>
                                <dd class="font-semibold text-emerald-600">−{{ number_format($summary['coupon_discount'], 2, ',', '.') }} ₺</dd>
                            </div>
                        @endif
                        @if ($postSubmitMethod === PaymentMethod::BankTransfer && $bankTransferDiscountPercent > 0)
                            <div class="flex justify-between gap-3">
                                <dt class="text-ink-2">Havale indirimi (%{{ rtrim(rtrim(number_format($bankTransferDiscountPercent, 2, '.', ''), '0'), '.') }})</dt>
                                <dd class="font-semibold text-emerald-600">−{{ number_format(max(0, $summary['total'] - ($payment->amount ?? $summary['total'])), 2, ',', '.') }} ₺</dd>
                            </div>
                        @endif
                        <div class="flex justify-between gap-3 border-t border-ink/10 pt-3">
                            <dt class="font-display text-base font-semibold text-ink">Ödenecek Tutar</dt>
                            @if ($postSubmitMethod)
                                <dd class="font-display text-base font-bold text-ink">{{ number_format((float) ($payment->amount ?? $summary['total']), 2, ',', '.') }} ₺</dd>
                            @else
                                <dd class="font-display text-base font-bold text-ink" x-text="(payable[tab] ?? 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺'"></dd>
                            @endif
                        </div>
                    </dl>

                    @unless ($postSubmitMethod)
                        <a href="{{ route('cart.index') }}" class="mt-4 block text-center text-xs font-medium text-ink-3 hover:text-ink">Sepete dön</a>
                    @endunless
                </div>
            </aside>
        </div>
    </div>
@endsection
