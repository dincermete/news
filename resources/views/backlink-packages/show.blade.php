@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var \App\Models\BacklinkPackage $package */
    /** @var \Illuminate\Support\Collection<int, \App\Models\BacklinkPackage> $relatedPackages */
    /** @var \Illuminate\Support\Collection<int, \App\Models\BlogPost> $latestBlogPosts */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SiteReview> $reviews */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SiteQuestion> $questions */

    $money = fn (float $amount): string => number_format($amount, 0, ',', '.').' ₺';
    $featureList = collect($package->features ?? [])->map(function ($feature) {
        return is_array($feature) ? ($feature['feature'] ?? reset($feature) ?: null) : $feature;
    })->filter()->values();

    $shortDescription = filled($package->description)
        ? \Illuminate\Support\Str::limit($package->description, 140)
        : null;

    $metaFacts = collect([
        filled($package->competition_label) ? $package->competition_label : null,
        'Tek seferlik ödeme',
        $package->is_featured ? 'Öne çıkan' : null,
    ])->filter()->values();

    $glyphs = [
        'link' => 'M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244',
        'shield' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
        'list' => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
    ];

    $trustStrip = collect([
        [
            'label' => 'Paket Tutarı',
            'value' => $money((float) $package->price),
            'tint' => 'from-[#fff8f3] to-white',
            'gradient' => 'from-[#fa8837] to-[#faac75]',
            'icon' => $glyphs['shield'],
        ],
        [
            'label' => 'Rekabet',
            'value' => filled($package->competition_label) ? $package->competition_label : 'Standart',
            'tint' => 'from-[#eef3ff] to-white',
            'gradient' => 'from-[#2248ab] to-[#7aa2ff]',
            'icon' => $glyphs['link'],
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
            <a href="{{ route('backlink-packages.index') }}" class="transition hover:text-ink">Backlink Paketleri</a>
            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            <span class="truncate text-ink-2">{{ $package->name }}</span>
        </nav>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <div class="grid min-w-0 gap-5 sm:gap-6 lg:grid-cols-[1.75fr_.85fr] lg:items-start" data-reveal-group>
            <div class="contents lg:flex lg:min-w-0 lg:flex-col lg:gap-5">
                <div class="{{ $card }} p-5 sm:p-6" style="order: 1" data-reveal>
                    <div class="flex items-start gap-3 sm:gap-x-4">
                        <span class="inline-flex size-[60px] shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#fa8837] to-[#faac75] text-white shadow-soft">
                            <svg class="size-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $glyphs['link'] }}"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h1 class="break-words font-display text-xl font-medium leading-tight text-ink sm:truncate sm:text-2xl">{{ $package->name }}</h1>
                            <p class="mt-1 text-sm text-ink-2">Backlink Paketi</p>
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
                                gradient="from-[#fa8837] to-[#faac75]"
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
                        :review-action="route('backlink-packages.review', $package)"
                        :question-action="route('backlink-packages.question', $package)"
                    />
                </div>
            </div>

            <div class="contents lg:sticky lg:top-28 lg:flex lg:min-w-0 lg:flex-col lg:gap-5">
                <div
                    class="{{ $card }} p-5 sm:p-6"
                    style="order: 2; background: linear-gradient(160deg, #fff8f3 0%, #ffffff 55%, #f0f4fc 100%);"
                    data-reveal
                >
                    <p class="font-display text-2xl font-semibold text-ink">{{ $money((float) $package->price) }}</p>
                    <p class="mt-1 text-xs text-ink-3">Tek seferlik ödeme (+ KDV)</p>

                    <div class="mt-5 space-y-2.5">
                        <form method="post" action="{{ route('cart.add') }}">
                            @csrf
                            <input type="hidden" name="product_type" value="backlink_package">
                            <input type="hidden" name="backlink_package_id" value="{{ $package->id }}">
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
                            <input type="hidden" name="product_type" value="backlink_package">
                            <input type="hidden" name="backlink_package_id" value="{{ $package->id }}">
                            <input type="hidden" name="redirect" value="checkout">
                            @guest
                                <button type="button" class="{{ $ctaBuy }}" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">
                                    Hemen Satın Al
                                </button>
                            @else
                                <button type="submit" class="{{ $ctaBuy }}">Hemen Satın Al</button>
                            @endguest
                        </form>

                        @if (filled($whatsappUrl))
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="{{ $ctaWhatsapp }}">
                                WhatsApp Sipariş Hattı
                            </a>
                        @endif
                    </div>

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
                        @if (filled($package->competition_label))
                            <div class="flex items-center justify-between gap-3">
                                <span>Rekabet seviyesi</span>
                                <span class="shrink-0 text-ink-3">{{ $package->competition_label }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between gap-3">
                            <span>Doğal anchor dağılımı</span>
                            <svg class="size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Tek seferlik ödeme</span>
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
                    gradient="from-[#fa8837] to-[#faac75]"
                    icon="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"
                    data-reveal
                >İlgili Ürünler</x-section-heading>

                <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedPackages as $related)
                        <div data-reveal>
                            <x-backlink-package-card :package="$related" :feature-limit="4" />
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    @endif
@endsection
