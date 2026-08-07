@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var \App\Models\SeoPackage $package */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SeoPackageDurationOption> $durationOptions */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SeoPackage> $relatedPackages */
    /** @var \Illuminate\Support\Collection<int, \App\Models\BlogPost> $latestBlogPosts */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SiteReview> $reviews */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SiteQuestion> $questions */

    $money = fn (float $amount): string => number_format($amount, 0, ',', '.').' ₺';
    $firstOption = $durationOptions->first();
    $featureList = collect($package->features ?? [])->map(function ($feature) {
        return is_array($feature) ? ($feature['feature'] ?? reset($feature) ?: null) : $feature;
    })->filter()->values();

    $shortDescription = filled($package->description)
        ? \Illuminate\Support\Str::limit($package->description, 140)
        : null;

    $metaFacts = collect([
        $package->keyword_count.' anahtar kelime',
        'Aylık '.$money((float) $package->monthly_price),
        $package->is_featured ? 'Öne çıkan' : null,
    ])->filter()->values();

    $glyphs = [
        'key' => 'M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z',
        'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
        'list' => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
    ];

    $trustStrip = collect([
        [
            'label' => 'Anahtar Kelime',
            'value' => (string) $package->keyword_count,
            'tint' => 'from-[#eef3ff] to-white',
            'gradient' => 'from-[#2248ab] to-[#7aa2ff]',
            'icon' => $glyphs['key'],
        ],
        [
            'label' => 'Aylık Başlangıç',
            'value' => $money((float) $package->monthly_price),
            'tint' => 'from-[#fff8f3] to-white',
            'gradient' => 'from-[#fa8837] to-[#faac75]',
            'icon' => $glyphs['calendar'],
        ],
        [
            'label' => 'Özellik',
            'value' => (string) $featureList->count(),
            'tint' => 'from-[#f0fdfa] to-white',
            'gradient' => 'from-[#0d9488] to-[#5eead4]',
            'icon' => $glyphs['list'],
        ],
    ]);

    $card = 'min-w-0 overflow-hidden rounded-[20px] border border-ink/10 bg-white shadow-soft';
    $ctaBase = 'inline-flex w-full items-center justify-center gap-x-2 rounded-xl px-4 py-3.5 text-sm font-semibold transition active:scale-[0.99]';
    $ctaCart = $ctaBase.' bg-gradient-to-b from-black to-[#363b3c] text-white hover:scale-[1.02]';
    $ctaBuy = $ctaBase.' border border-ink/15 bg-white text-ink hover:bg-paper';
    $ctaWhatsapp = $ctaBase.' border border-emerald-500/40 bg-white text-emerald-700 hover:bg-emerald-50';
    $outlineBtn = 'inline-flex w-full items-center justify-center gap-x-1.5 rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm font-medium text-ink transition hover:border-ink/20 hover:bg-paper';
@endphp

@section('content')
    <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8" data-reveal-group>
        <nav class="flex items-center gap-x-1.5 text-xs text-ink-3" aria-label="Konum" data-reveal>
            <a href="{{ route('home') }}" class="transition hover:text-ink">Anasayfa</a>
            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            <a href="{{ route('seo-packages.index') }}" class="transition hover:text-ink">SEO Paketleri</a>
            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            <span class="truncate text-ink-2">{{ $package->name }}</span>
        </nav>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <div class="grid min-w-0 gap-5 sm:gap-6 lg:grid-cols-[1.75fr_.85fr] lg:items-start" data-reveal-group>
            <div class="contents lg:flex lg:min-w-0 lg:flex-col lg:gap-5">
                <div class="{{ $card }} p-5 sm:p-6" style="order: 1" data-reveal>
                    <div class="flex items-start gap-3 sm:gap-x-4">
                        <span class="inline-flex size-[60px] shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#2248ab] to-[#7aa2ff] text-white shadow-soft">
                            <svg class="size-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $glyphs['key'] }}"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h1 class="break-words font-display text-xl font-medium leading-tight text-ink sm:truncate sm:text-2xl">{{ $package->name }}</h1>
                            <p class="mt-1 text-sm text-ink-2">SEO Paketi</p>
                        </div>
                    </div>

                    @if ($shortDescription)
                        <p class="mt-4 max-w-2xl text-sm leading-relaxed text-ink-2">{{ $shortDescription }}</p>
                    @endif

                    @if ($metaFacts->isNotEmpty())
                        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-ink/10 pt-4">
                            @foreach ($metaFacts as $fact)
                                <span class="inline-flex items-center rounded-full bg-paper px-2.5 py-1 text-xs font-medium text-ink-2">
                                    {{ $fact }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="grid gap-3 sm:grid-cols-3" style="order: 3" data-reveal>
                    @foreach ($trustStrip as $metric)
                        <div class="rounded-[20px] border border-ink/10 bg-gradient-to-br {{ $metric['tint'] }} p-5 shadow-soft sm:p-6">
                            <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-[10px] bg-gradient-to-br text-white {{ $metric['gradient'] }}">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $metric['icon'] }}" />
                                </svg>
                            </span>
                            <p class="mt-3 font-display text-2xl font-semibold tabular-nums tracking-tight text-ink sm:text-3xl">
                                {{ $metric['value'] }}
                            </p>
                            <p class="mt-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-3">
                                {{ $metric['label'] }}
                            </p>
                        </div>
                    @endforeach
                </div>

                @if ($featureList->isNotEmpty())
                    <div class="{{ $card }}" style="order: 5" data-reveal>
                        <div class="border-b border-ink/10 px-5 py-4 sm:px-6">
                            <x-section-heading
                                size="sm"
                                gradient="from-[#2248ab] to-[#7aa2ff]"
                                icon="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                            >Paket Özellikleri</x-section-heading>
                        </div>
                        <ul class="grid gap-2 p-4 sm:grid-cols-2 sm:p-5">
                            @foreach ($featureList as $feature)
                                <li class="flex items-start gap-2.5 rounded-lg bg-paper px-4 py-3 text-sm font-medium text-ink-2">
                                    <svg class="mt-0.5 size-4 shrink-0 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div style="order: 6" data-reveal>
                    <x-product-engagement-tabs
                        :description-text="$package->description"
                        empty-description="Bu paket için henüz açıklama eklenmedi."
                        :delivery-details="site_setting()->defaultDeliveryDetails()"
                        :reviews="$reviews"
                        :questions="$questions"
                        :review-action="route('seo-packages.review', $package)"
                        :question-action="route('seo-packages.question', $package)"
                    />
                </div>
            </div>

            <div class="contents lg:sticky lg:top-28 lg:flex lg:min-w-0 lg:flex-col lg:gap-5">
                <div
                    class="{{ $card }} p-5 sm:p-6"
                    style="order: 2; background: linear-gradient(160deg, #fff8f3 0%, #ffffff 55%, #f0f4fc 100%);"
                    data-reveal
                >
                    <p class="font-display text-2xl font-semibold text-ink">{{ $money((float) $package->monthly_price) }}<span class="text-base font-medium text-ink-3"> / ay</span></p>

                    @if ($firstOption)
                        <div class="mt-5 space-y-2.5" x-data="{ durationId: '{{ $firstOption->id }}' }">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Süre</label>
                                <select
                                    x-model="durationId"
                                    class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink focus:ring-0"
                                >
                                    @foreach ($durationOptions as $option)
                                        <option value="{{ $option->id }}">
                                            {{ $option->name ?? ($option->months.' ay') }} — {{ $money($option->resolvePrice($package->monthly_price)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <form method="post" action="{{ route('cart.add') }}">
                                @csrf
                                <input type="hidden" name="product_type" value="seo_package">
                                <input type="hidden" name="seo_package_id" value="{{ $package->id }}">
                                <input type="hidden" name="seo_package_duration_option_id" x-bind:value="durationId">
                                <input type="hidden" name="redirect" value="cart">
                                @guest
                                    <button type="button" class="{{ $ctaCart }}" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">
                                        Sepete Ekle
                                    </button>
                                @else
                                    <button type="submit" class="{{ $ctaCart }}">Sepete Ekle</button>
                                @endguest
                            </form>

                            <form method="post" action="{{ route('cart.add') }}">
                                @csrf
                                <input type="hidden" name="product_type" value="seo_package">
                                <input type="hidden" name="seo_package_id" value="{{ $package->id }}">
                                <input type="hidden" name="seo_package_duration_option_id" x-bind:value="durationId">
                                <input type="hidden" name="redirect" value="checkout">
                                @guest
                                    <button type="button" class="{{ $ctaBuy }}" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">
                                        Hemen Satın Al
                                    </button>
                                @else
                                    <button type="submit" class="{{ $ctaBuy }}">Hemen Satın Al</button>
                                @endguest
                            </form>
                        </div>
                    @endif

                    @if (filled($whatsappUrl))
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="{{ $ctaWhatsapp }} mt-2.5">
                            WhatsApp Sipariş Hattı
                        </a>
                    @endif

                    <div class="mt-3">
                        <button
                            type="button"
                            class="{{ $outlineBtn }}"
                            data-share-url="{{ $package->canonicalUrl() }}"
                            onclick="const btn=this; const url=btn.dataset.shareUrl; if (navigator.share) { navigator.share({title: document.title, url}); } else { navigator.clipboard.writeText(url); const label = btn.querySelector('[data-share-label]'); const original = label.textContent; label.textContent = 'Kopyalandı'; setTimeout(() => { label.textContent = original; }, 1500); }"
                        >
                            <span data-share-label>Paylaş</span>
                        </button>
                    </div>

                    <div class="mt-5 space-y-2 border-t border-ink/10 pt-5 text-sm text-ink-2">
                        <div class="flex items-center justify-between gap-3">
                            <span>Anahtar kelime</span>
                            <span class="shrink-0 text-ink-3">{{ $package->keyword_count }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Aylık paket</span>
                            <svg class="size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Özelleştirilebilir süre</span>
                            <svg class="size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </div>
                    </div>
                </div>

                <x-product-blog-sidebar :posts="$latestBlogPosts" style="order: 7" data-reveal />
            </div>
        </div>
    </section>

    @if ($relatedPackages->isNotEmpty())
        <div class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8">
            <section data-reveal-group>
                <x-section-heading
                    size="sm"
                    gradient="from-[#2248ab] to-[#7aa2ff]"
                    icon="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"
                    data-reveal
                >İlgili Ürünler</x-section-heading>

                <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedPackages as $related)
                        <a href="{{ $related->canonicalUrl() }}" class="{{ $card }} block p-5 transition hover:-translate-y-0.5 hover:shadow-pop" data-reveal>
                            <h3 class="font-display text-base font-semibold text-ink">{{ $related->name }}</h3>
                            <p class="mt-2 text-sm text-ink-2">{{ $related->keyword_count }} anahtar kelime</p>
                            <p class="mt-3 font-display text-xl font-semibold text-ink">{{ $money((float) $related->monthly_price) }}<span class="text-sm font-medium text-ink-3"> / ay</span></p>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    @endif
@endsection
