@extends('layouts.account')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('content')
    @php
        $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
        $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
        $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
        $btnDark = 'group inline-flex items-center justify-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 py-3 text-sm font-semibold text-white transition hover:scale-[1.01] active:scale-[0.98]';
        $tone = fn (?string $color) => match ($color) {
            'success' => 'bg-emerald-100 text-emerald-700',
            'warning' => 'bg-amber-100 text-amber-700',
            'danger' => 'bg-brand-100 text-brand-700',
            'info', 'primary' => 'bg-accent-100 text-accent-700',
            default => 'bg-ink/5 text-ink-3',
        };
        $payment = $orderGroup->payments->first();
    @endphp

    <div class="space-y-5" x-data="{ tab: 'detay', editingBilling: {{ $orderGroup->billingProfile ? 'false' : 'true' }} }">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p><span class="{{ $chip }}">Hesabım</span></p>
                <h1 class="mt-3 font-display text-2xl font-medium text-ink sm:text-[28px]">Sipariş #{{ $orderGroup->id }}</h1>
            </div>
            <a href="{{ route('account.orders') }}" class="text-sm font-medium text-ink-3 hover:text-ink">Siparişlerime dön</a>
        </div>

        @if (session('status'))
            <div class="rounded-[20px] border border-emerald-200 bg-emerald-50 px-5 py-3.5 text-sm font-medium text-emerald-800" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-[20px] border border-brand-200 bg-brand-50 px-5 py-3.5 text-sm text-brand-800" role="alert">
                <ul class="list-disc space-y-0.5 ps-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="inline-flex items-center gap-1 rounded-2xl border border-ink/10 bg-white p-1">
            <button type="button" class="rounded-xl px-4 py-2 text-sm font-semibold transition" :class="tab === 'detay' ? 'bg-ink text-white' : 'text-ink-2 hover:text-ink'" @click="tab = 'detay'">
                Sipariş Detayı
            </button>
            <button type="button" class="rounded-xl px-4 py-2 text-sm font-semibold transition" :class="tab === 'fatura' ? 'bg-ink text-white' : 'text-ink-2 hover:text-ink'" @click="tab = 'fatura'">
                Fatura
            </button>
        </div>

        <div x-show="tab === 'detay'" x-cloak class="space-y-5">
            <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                <div class="flex flex-wrap items-center gap-3">
                    @if ($payment)
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $tone($payment->status?->getColor()) }}">
                            {{ $payment->method?->getLabel() }} — {{ $payment->status?->getLabel() }}
                        </span>
                    @endif
                    <span class="text-sm text-ink-2">{{ $orderGroup->created_at?->format('d.m.Y H:i') }}</span>
                </div>
                <dl class="mt-4 space-y-2 text-sm">
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
                            <dt class="text-ink-2">Kupon indirimi</dt>
                            <dd class="font-semibold text-emerald-600">−{{ number_format((float) $orderGroup->coupon_discount_amount, 2, ',', '.') }} ₺</dd>
                        </div>
                    @endif
                    <div class="flex justify-between gap-3 border-t border-ink/10 pt-2">
                        <dt class="font-semibold text-ink">Toplam</dt>
                        <dd class="font-display font-bold text-ink">{{ number_format((float) $orderGroup->total, 2, ',', '.') }} ₺</dd>
                    </div>
                </dl>
            </div>

            <div class="space-y-3">
                @foreach ($orderGroup->orders as $order)
                    @php
                        $payload = is_array($order->content_payload) ? $order->content_payload : [];
                        $orderTitle = $order->site?->domain
                            ?? $order->siteBundle?->name
                            ?? $order->instagramAccount?->handle
                            ?? $order->product_type?->getLabel()
                            ?? 'Ürün #'.$order->id;
                    @endphp
                    <div class="rounded-[20px] border border-ink/10 bg-white p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($order->site)
                                        <x-site-favicon :domain="$order->site->domain" :size="24" class="shrink-0 rounded-md" />
                                    @elseif ($order->instagramAccount)
                                        <x-instagram-avatar :account="$order->instagramAccount" :size="24" />
                                    @endif
                                    <p class="font-semibold text-ink">{{ $orderTitle }}</p>
                                </div>
                                <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                    @include('partials.cart-item-badge', ['item' => $order])
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $tone($order->status?->getColor()) }}">
                                        {{ $order->status?->getLabel() }}
                                    </span>
                                </div>
                            </div>
                            <span class="shrink-0 font-display text-sm font-bold text-ink">{{ number_format((float) $order->price, 2, ',', '.') }} {{ $order->currency?->value ?? 'TRY' }}</span>
                        </div>

                        @if (! empty($payload))
                            <dl class="mt-4 grid gap-2 border-t border-ink/10 pt-4 text-xs sm:grid-cols-2">
                                @if (! empty($payload['target_url']))
                                    <div><dt class="text-ink-3">Hedef URL</dt><dd class="text-ink">{{ $payload['target_url'] }}</dd></div>
                                @endif
                                @if (! empty($payload['keywords']))
                                    <div><dt class="text-ink-3">Anahtar kelimeler</dt><dd class="text-ink">{{ $payload['keywords'] }}</dd></div>
                                @endif
                                @if (! empty($payload['publish_at']))
                                    <div><dt class="text-ink-3">Yayın zamanı</dt><dd class="text-ink">{{ $payload['publish_at'] }}</dd></div>
                                @endif
                                @if (! empty($payload['file_path']))
                                    <div><dt class="text-ink-3">Dosya</dt><dd class="text-ink">{{ basename($payload['file_path']) }}</dd></div>
                                @endif
                                @if (! empty($payload['image_path']))
                                    <div><dt class="text-ink-3">Görsel</dt><dd class="text-ink">{{ basename($payload['image_path']) }}</dd></div>
                                @endif
                                @if (! empty($payload['brief']))
                                    <div class="sm:col-span-2"><dt class="text-ink-3">Brief</dt><dd class="text-ink">{{ $payload['brief'] }}</dd></div>
                                @endif
                                @if (! empty($payload['note']))
                                    <div class="sm:col-span-2"><dt class="text-ink-3">Not</dt><dd class="text-ink">{{ $payload['note'] }}</dd></div>
                                @endif
                            </dl>
                        @endif

                        @if ($order->publishedLink)
                            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-800">
                                <p class="font-semibold">Yayında: <a href="{{ $order->publishedLink->published_url }}" target="_blank" class="underline underline-offset-2">{{ $order->publishedLink->published_url }}</a></p>
                                <p class="mt-1">
                                    {{ $order->publishedLink->published_at?->format('d.m.Y') }}
                                    @if ($order->publishedLink->guarantee_until)
                                        — garanti bitişi {{ $order->publishedLink->guarantee_until->format('d.m.Y') }}
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div x-show="tab === 'fatura'" x-cloak class="space-y-5">
            @if (! $orderGroup->billingProfile)
                <div class="rounded-[20px] border border-ink/10 bg-paper p-6 text-center sm:p-8">
                    <span class="mx-auto inline-flex size-12 items-center justify-center rounded-full bg-accent-100 text-accent-600">
                        <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3-15H9.75A2.25 2.25 0 0 0 7.5 5.25v13.5A2.25 2.25 0 0 0 9.75 21h4.5a2.25 2.25 0 0 0 2.25-2.25V6.108c0-.318-.126-.622-.351-.847L14.643 3.75a1.5 1.5 0 0 0-1.06-.44H12"/></svg>
                    </span>
                    <h2 class="mt-4 font-display text-lg font-semibold text-ink">Fatura bilgisi eksik</h2>
                    <p class="mt-2 text-sm text-ink-2">Bu sipariş için fatura istiyorsanız faturalandırma bilgilerinizi aşağıdan tamamlayın.</p>
                </div>
            @else
                <div x-show="!editingBilling" x-cloak class="rounded-[20px] border border-ink/10 bg-white p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-display text-base font-semibold text-ink">Fatura bilgileri</h2>
                        <button type="button" class="text-xs font-semibold text-accent-700 hover:text-accent-800" @click="editingBilling = true">Düzenle</button>
                    </div>
                    <dl class="mt-3 space-y-1.5 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-ink-2">Tip</dt><dd class="text-ink">{{ $orderGroup->billingProfile->type?->getLabel() }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-ink-2">TCKN / VKN</dt><dd class="text-ink">{{ $orderGroup->billingProfile->tax_id }}</dd></div>
                        @if ($orderGroup->billingProfile->company_name)
                            <div class="flex justify-between gap-3"><dt class="text-ink-2">Ünvan</dt><dd class="text-ink">{{ $orderGroup->billingProfile->company_name }}</dd></div>
                        @endif
                        <div class="flex justify-between gap-3"><dt class="text-ink-2">Adres</dt><dd class="text-end text-ink">{{ $orderGroup->billingProfile->address }}</dd></div>
                        @if ($orderGroup->billingProfile->tax_office)
                            <div class="flex justify-between gap-3"><dt class="text-ink-2">Vergi dairesi</dt><dd class="text-ink">{{ $orderGroup->billingProfile->tax_office }}</dd></div>
                        @endif
                    </dl>

                    @if ($invoice)
                        <a href="{{ route('account.invoices.download', $invoice) }}" class="{{ $btnDark }} mt-5 w-full">
                            <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            </span>
                            Faturayı indir ({{ $invoice->invoice_number }})
                        </a>
                    @else
                        <p class="mt-4 text-xs text-ink-3">Fatura, ödemeniz onaylandığında otomatik oluşturulacak.</p>
                    @endif
                </div>
            @endif

            <div x-show="editingBilling" x-cloak>
                @include('account.partials.billing-profile-form', ['orderGroup' => $orderGroup, 'billingProfiles' => $billingProfiles])
            </div>
        </div>
    </div>
@endsection
