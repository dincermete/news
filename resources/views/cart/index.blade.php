@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var \App\Models\Cart $cart */
    /** @var array $summary */
    $currency = 'TRY';

    $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    $btnDark = 'group inline-flex w-full items-center justify-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 py-3 text-sm font-semibold text-white transition hover:scale-[1.01] active:scale-[0.98]';
    $btnChip = 'inline-flex size-9 items-center justify-center rounded-xl';

    $unconfiguredCount = $cart->items->filter(fn ($item) => ! $item->isConfigured())->count();
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-5 py-9 sm:px-8" data-reveal-group>
                <div data-reveal>
                    <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80">
                        <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Sepet</span>
                        {{ $cart->items->count() }} ürün
                    </p>
                    <h1 class="mt-4 font-display text-3xl font-medium leading-tight sm:text-4xl">Sepetiniz</h1>
                </div>
                <a href="{{ route('sites.index') }}" class="group inline-flex items-center gap-x-2 rounded-2xl border border-white/15 bg-white/5 px-5 py-3 text-sm font-medium text-white transition hover:bg-white/10" data-reveal>
                    <svg class="size-3.5 transition group-hover:-translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kataloğa dön
                </a>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-[20px] border border-emerald-200 bg-emerald-50 px-5 py-3.5 text-sm font-medium text-emerald-800" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-[20px] border border-brand-200 bg-brand-50 px-5 py-3.5 text-sm text-brand-800" role="alert">
                <ul class="list-disc space-y-0.5 ps-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($cart->items->isEmpty())
            <div class="rounded-[20px] border border-ink/10 bg-paper px-6 py-16 text-center">
                <p class="font-display text-lg font-semibold text-ink">Sepetiniz boş.</p>
                <p class="mt-1.5 text-sm text-ink-2">Katalogdan dilediğiniz siteyi seçip sepete ekleyin.</p>
                <a href="{{ route('sites.index') }}" class="{{ $btnDark }} mt-5 w-auto px-5">
                    <span class="{{ $btnChip }} bg-white/15 text-white">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </span>
                    Siteleri incele
                </a>
            </div>
        @else
            @if ($unconfiguredCount > 0)
                <div class="mb-6 flex items-start gap-3 rounded-[20px] border border-orange-200 bg-orange-50 p-5">
                    <span class="mt-0.5 inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-orange-100 text-orange-600">
                        <svg class="size-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z"/></svg>
                    </span>
                    <div>
                        <p class="font-display text-sm font-semibold text-orange-900">{{ $unconfiguredCount }} ürün yapılandırma bekliyor</p>
                        <p class="mt-1 text-sm text-orange-800">
                            Tanıtım yazılarınız için makale/dosya yüklemeniz veya yazım seçeneğini belirlemeniz gerekiyor. Her ürünün yanındaki
                            <span class="font-semibold text-orange-900">Yapılandır</span> butonuna tıklayın, yapılandırılmadan ödeme adımına geçilemez.
                        </p>
                    </div>
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
                        <div class="flex items-center justify-between border-b border-ink/10 px-5 py-4">
                            <h2 class="font-display text-base font-semibold text-ink">Ürünler</h2>
                            <span class="text-xs font-medium text-ink-3">{{ $cart->items->count() }} ürün</span>
                        </div>

                        <div class="divide-y divide-ink/5">
                            @foreach ($cart->items as $item)
                                @php
                                    $title = $item->site?->domain
                                        ?? $item->siteBundle?->name
                                        ?? $item->instagramAccount?->handle
                                        ?? $item->seoPackage?->name
                                        ?? $item->product_type?->getLabel()
                                        ?? 'Ürün #'.$item->id;
                                    $configured = $item->isConfigured();
                                @endphp
                                <div class="px-5 py-4" x-data="{ open: false }">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex min-w-0 items-center gap-x-3">
                                            @if ($item->site)
                                                <x-site-favicon :domain="$item->site->domain" :size="40" class="shrink-0 rounded-xl" />
                                            @elseif ($item->instagramAccount)
                                                <x-instagram-avatar :account="$item->instagramAccount" :size="40" class="shrink-0" />
                                            @else
                                                <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-600">
                                                    <svg class="size-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                                </span>
                                            @endif
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="truncate text-sm font-semibold text-ink">{{ $title }}</p>
                                                    @include('partials.cart-item-badge', ['item' => $item])
                                                </div>
                                                <div class="mt-1.5">
                                                    @if ($configured)
                                                        <span class="inline-flex items-center gap-x-1 rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                                            Yapılandırıldı
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-x-1 rounded-full bg-orange-100 px-2.5 py-1 text-[11px] font-semibold text-orange-700">
                                                            <span class="size-1.5 rounded-full bg-orange-500"></span>
                                                            Yapılandırma bekliyor
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex shrink-0 items-center gap-2">
                                            <p class="font-display text-sm font-bold text-ink">
                                                {{ number_format((float) $item->price, 2, ',', '.') }} {{ $item->currency?->value ?? $currency }}
                                            </p>
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-x-1.5 rounded-full px-3.5 py-2 text-xs font-semibold text-white transition hover:scale-[1.03] active:scale-[0.98] {{ $configured ? 'bg-ink hover:bg-black' : 'bg-gradient-to-b from-orange-500 to-orange-600' }}"
                                                @click="open = true"
                                            >
                                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                                                {{ $configured ? 'Düzenle' : 'Yapılandır' }}
                                            </button>
                                            <form method="post" action="{{ route('cart.remove', $item) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex size-8 items-center justify-center rounded-xl border border-ink/10 text-ink-2 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600" aria-label="Ürünü kaldır">
                                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- ============ YAPILANDIRMA MODALI ============ --}}
                                    <div
                                        x-show="open"
                                        x-cloak
                                        class="fixed inset-0 z-[70] flex items-center justify-center bg-ink/40 p-4 backdrop-blur-sm"
                                        @keydown.escape.window="open = false"
                                        role="dialog"
                                        aria-modal="true"
                                    >
                                        <div
                                            class="max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-[20px] bg-white shadow-pop"
                                            @click.outside="open = false"
                                        >
                                            <div class="flex items-center justify-between gap-3 border-b border-ink/10 px-5 py-4">
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold text-ink">{{ $title }}</p>
                                                    <p class="text-xs text-ink-3">İçeriğinizi yapılandırın</p>
                                                </div>
                                                <button type="button" class="inline-flex size-8 shrink-0 items-center justify-center rounded-xl text-ink-2 transition hover:bg-paper hover:text-ink" @click="open = false" aria-label="Kapat">
                                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>

                                            @switch($item->product_type)
                                                @case(\App\Enums\ProductType::FooterLink)
                                                    @include('cart.partials.footer-link-editor', ['item' => $item])
                                                    @break
                                                @case(\App\Enums\ProductType::Story)
                                                    @include('cart.partials.story-editor', ['item' => $item])
                                                    @break
                                                @case(\App\Enums\ProductType::SeoPackage)
                                                    @include('cart.partials.seo-package-editor', ['item' => $item])
                                                    @break
                                                @default
                                                    @include('cart.partials.content-editor-tabs', ['item' => $item, 'wordPackages' => $wordPackages])
                                            @endswitch
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <aside class="space-y-4 lg:col-span-1">
                    <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                        <h2 class="font-display text-base font-semibold text-ink">Kupon</h2>
                        <form method="post" action="{{ route('cart.apply-coupon') }}" class="mt-3 flex gap-2">
                            @csrf
                            <input
                                type="text"
                                name="coupon_code"
                                value="{{ old('coupon_code', $summary['coupon_code'] ?? '') }}"
                                placeholder="Kod"
                                class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink placeholder:text-ink-3 focus:border-ink/30 focus:ring-0"
                            >
                            <button type="submit" class="shrink-0 rounded-xl border border-ink/10 bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:border-ink/25">
                                Uygula
                            </button>
                        </form>
                        @error('coupon_code')
                            <p class="mt-2.5 text-xs font-medium text-brand-600">{{ $message }}</p>
                        @enderror
                        @if ($summary['coupon'])
                            <p class="mt-2.5 inline-flex items-center gap-x-1.5 text-xs font-semibold text-emerald-700">
                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                {{ $summary['coupon']->code }} uygulandı (−{{ number_format($summary['coupon_discount'], 2, ',', '.') }} ₺)
                            </p>
                        @endif
                    </div>

                    <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                        <h2 class="font-display text-base font-semibold text-ink">Sipariş özeti</h2>
                        <dl class="mt-4 space-y-2.5 text-sm">
                            <div class="flex justify-between gap-3">
                                <dt class="text-ink-2">Ara toplam</dt>
                                <dd class="font-semibold text-ink">{{ number_format($summary['subtotal'], 2, ',', '.') }} ₺</dd>
                            </div>
                            @if ($summary['tier_discount'] > 0)
                                <div class="flex justify-between gap-3">
                                    <dt class="text-ink-2">
                                        Kademe indirimi
                                        @if ($summary['tier'])
                                            <span class="text-[11px] text-ink-3">({{ rtrim(rtrim(number_format((float) $summary['tier']->discount_percentage, 2, '.', ''), '0'), '.') }}%)</span>
                                        @endif
                                    </dt>
                                    <dd class="font-semibold text-emerald-600">−{{ number_format($summary['tier_discount'], 2, ',', '.') }} ₺</dd>
                                </div>
                            @endif
                            @if ($summary['coupon_discount'] > 0)
                                <div class="flex justify-between gap-3">
                                    <dt class="text-ink-2">Kupon indirimi</dt>
                                    <dd class="font-semibold text-emerald-600">−{{ number_format($summary['coupon_discount'], 2, ',', '.') }} ₺</dd>
                                </div>
                            @endif
                            <div class="flex justify-between gap-3 border-t border-ink/10 pt-3">
                                <dt class="font-display text-base font-semibold text-ink">Genel toplam</dt>
                                <dd class="font-display text-base font-bold text-ink">{{ number_format($summary['total'], 2, ',', '.') }} ₺</dd>
                            </div>
                        </dl>

                        @auth
                            @if ($unconfiguredCount > 0)
                                <button type="button" disabled class="{{ $btnDark }} mt-5 cursor-not-allowed opacity-50">
                                    <span class="{{ $btnChip }} bg-white/15 text-white">
                                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                    </span>
                                    Ödemeye geç
                                </button>
                                <p class="mt-2 text-center text-xs text-ink-3">Devam etmek için tüm ürünleri yapılandırın.</p>
                            @else
                                <a href="{{ route('checkout.show') }}" class="{{ $btnDark }} mt-5">
                                    <span class="{{ $btnChip }} bg-white/15 text-white">
                                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                    </span>
                                    Ödemeye geç
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="{{ $btnDark }} mt-5">
                                <span class="{{ $btnChip }} bg-white/15 text-white">
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                </span>
                                Giriş yap / ödeme
                            </a>
                        @endauth
                    </div>
                </aside>
            </div>
        @endif
    </div>
@endsection
