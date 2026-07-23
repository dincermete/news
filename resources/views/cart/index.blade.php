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
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-3 lg:col-span-2">
                    <div class="hs-accordion-group space-y-3" data-hs-accordion-always-open>
                        @foreach ($cart->items as $item)
                            @php
                                $payload = is_array($item->content_payload) ? $item->content_payload : [];
                                $mode = $item->content_mode?->value ?? \App\Enums\ContentMode::FileUpload->value;
                                $title = $item->site?->domain
                                    ?? $item->siteBundle?->name
                                    ?? $item->product_type?->getLabel()
                                    ?? 'Ürün #'.$item->id;
                            @endphp
                            <div class="hs-accordion active overflow-hidden rounded-[20px] border border-ink/10 bg-white" id="cart-item-heading-{{ $item->id }}">
                                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink/10 px-5 py-4">
                                    <div class="flex min-w-0 items-center gap-x-3">
                                        @if ($item->site)
                                            <x-site-favicon :domain="$item->site->domain" :size="32" class="shrink-0 rounded-lg" />
                                        @else
                                            <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-paper text-ink-3">
                                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25-2.25M12 13.875V6.375m0 0L9.75 8.625M12 6.375l2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                            </span>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center rounded-full bg-accent-100 px-2 py-0.5 text-[11px] font-semibold text-accent-700">
                                                    {{ $item->product_type?->getLabel() }}
                                                </span>
                                                <p class="truncate text-sm font-semibold text-ink">{{ $title }}</p>
                                            </div>
                                            <p class="mt-1 font-display text-sm font-bold text-ink">
                                                {{ number_format((float) $item->price, 2, ',', '.') }} {{ $item->currency?->value ?? $currency }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <button
                                            type="button"
                                            class="hs-accordion-toggle inline-flex items-center gap-x-1.5 rounded-xl border border-ink/10 bg-white px-3 py-2 text-xs font-semibold text-ink-2 transition hover:border-ink/25 hover:text-ink"
                                            aria-expanded="true"
                                            aria-controls="cart-item-collapse-{{ $item->id }}"
                                        >
                                            İçerik
                                            <svg class="hs-accordion-active:hidden size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                            <svg class="hs-accordion-active:block hidden size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
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

                                <div
                                    id="cart-item-collapse-{{ $item->id }}"
                                    class="hs-accordion-content w-full overflow-hidden transition-[height] duration-300"
                                    role="region"
                                    aria-labelledby="cart-item-heading-{{ $item->id }}"
                                >
                                    <div
                                        class="bg-paper p-5"
                                        x-data="{ tab: '{{ $mode === 'ai_article' ? 'ai' : 'file' }}' }"
                                    >
                                        <div class="inline-flex items-center gap-1 rounded-2xl border border-ink/10 bg-white p-1">
                                            <button
                                                type="button"
                                                class="rounded-xl px-3.5 py-2 text-xs font-semibold transition"
                                                :class="tab === 'file' ? 'bg-ink text-white' : 'text-ink-2 hover:text-ink'"
                                                @click="tab = 'file'"
                                            >
                                                Dosya Yükle
                                            </button>
                                            <button
                                                type="button"
                                                class="rounded-xl px-3.5 py-2 text-xs font-semibold transition"
                                                :class="tab === 'ai' ? 'bg-ink text-white' : 'text-ink-2 hover:text-ink'"
                                                @click="tab = 'ai'"
                                            >
                                                Makale Yazdır
                                            </button>
                                        </div>

                                        <form method="post" action="{{ route('cart.update-content', $item) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                                            @csrf
                                            @method('PATCH')

                                            <div class="space-y-3" x-show="tab === 'file'" x-cloak>
                                                <input type="hidden" name="content_mode" value="file_upload" :disabled="tab !== 'file'">
                                                <div>
                                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Dosya</label>
                                                    <input type="file" name="file" accept=".doc,.docx,.pdf,.txt,.rtf" class="block w-full rounded-xl border border-ink/10 bg-white text-sm text-ink file:me-3 file:rounded-lg file:border-0 file:bg-ink file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white" :disabled="tab !== 'file'">
                                                    @if (! empty($payload['file_path']))
                                                        <p class="mt-1.5 text-[11px] text-ink-3">Yüklü: {{ basename($payload['file_path']) }}</p>
                                                    @endif
                                                </div>
                                                <div>
                                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Hedef URL</label>
                                                    <input type="url" name="target_url" value="{{ old('target_url', $payload['target_url'] ?? '') }}" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" placeholder="https://" :disabled="tab !== 'file'">
                                                </div>
                                            </div>

                                            <div class="space-y-3" x-show="tab === 'ai'" x-cloak>
                                                <input type="hidden" name="content_mode" value="ai_article" :disabled="tab !== 'ai'">
                                                <div>
                                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Kelime paketi</label>
                                                    <select name="article_word_package_id" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" :disabled="tab !== 'ai'">
                                                        <option value="">Seçin</option>
                                                        @foreach ($wordPackages as $package)
                                                            <option value="{{ $package->id }}" @selected((int) old('article_word_package_id', $item->article_word_package_id) === (int) $package->id)>
                                                                {{ $package->word_count }} kelime — {{ number_format((float) $package->price, 2, ',', '.') }} ₺
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Anahtar kelimeler</label>
                                                    <input type="text" name="keywords" value="{{ old('keywords', $payload['keywords'] ?? '') }}" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" :disabled="tab !== 'ai'">
                                                </div>
                                                <div>
                                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Brief</label>
                                                    <textarea name="brief" rows="3" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" :disabled="tab !== 'ai'">{{ old('brief', $payload['brief'] ?? '') }}</textarea>
                                                </div>
                                                <div>
                                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Hedef URL</label>
                                                    <input type="url" name="target_url" value="{{ old('target_url', $payload['target_url'] ?? '') }}" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" placeholder="https://" :disabled="tab !== 'ai'">
                                                </div>
                                            </div>

                                            <button type="submit" class="inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]">
                                                Kaydet
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
                            <a href="{{ route('checkout.show') }}" class="{{ $btnDark }} mt-5">
                                <span class="{{ $btnChip }} bg-white/15 text-white">
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                </span>
                                Ödemeye geç
                            </a>
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
