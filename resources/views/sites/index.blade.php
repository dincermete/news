@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var \App\Support\SiteCatalogFilters $filters */
    /** @var \Illuminate\Pagination\LengthAwarePaginator<\App\Models\Site> $sites */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SiteCategory> $categories */
    /** @var int $activeSiteCount */

    $fmt = fn (int $n): string => number_format($n, 0, ',', '.');
    $money = function (float $amount, string $currency): string {
        $symbol = $currency === 'TRY' ? '₺' : '$';
        $formatted = fmod($amount, 1.0) > 0.009
            ? number_format($amount, 2, ',', '.')
            : number_format($amount, 0, ',', '.');

        return $formatted.$symbol;
    };

    // Kategori pilleri ve filtre formu, mevcut filtreleri korumak için ortak parametre seti kullanır.
    $baseParams = collect($filters->toQueryParameters());
    $categoryUrl = fn (?string $slug) => route('sites.index', $baseParams->except('kategori')->when($slug, fn ($c) => $c->put('kategori', $slug))->all());

    $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    $sortOptions = [
        'price_asc' => 'Fiyat: Artan',
        'price_desc' => 'Fiyat: Azalan',
        'da_asc' => 'DA: Artan',
        'da_desc' => 'DA: Azalan',
        'newest' => 'En Yeni',
    ];
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto flex max-w-6xl flex-col items-center px-5 pb-10 pt-14 text-center sm:px-8 sm:pb-12 sm:pt-16" data-reveal-group>
                <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80" data-reveal>
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Katalog</span>
                    {{ $fmt($activeSiteCount) }}+ aktif haber sitesi
                </p>

                <h1 class="mt-5 max-w-2xl font-display text-4xl font-medium leading-[1.12] sm:text-5xl" data-reveal>
                    Markanıza uygun siteyi seçin, yayına hazırlayın
                </h1>

                <p class="mt-4 max-w-xl text-lg font-medium leading-relaxed text-white/65" data-reveal>
                    Kategori, fiyat ve DA/PA'ya göre filtreleyin; sepete ekleyip aynı gün sipariş oluşturun.
                </p>

                <form method="get" action="{{ route('sites.index') }}" class="mt-7 flex w-full max-w-xl items-center gap-2 rounded-full border border-white/15 bg-white p-1.5 shadow-pop" role="search">
                    @foreach ($baseParams->except('q') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <svg class="ms-3 size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters->q }}"
                        placeholder="Örn. habergazetesi.com.tr"
                        class="w-full border-0 bg-transparent p-0 py-2 text-sm text-ink placeholder:text-ink-3 focus:ring-0"
                        aria-label="Site ara"
                    >
                    <button type="submit" class="inline-flex shrink-0 items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.03] active:scale-[0.98]">
                        Ara
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </button>
                </form>

                @if ($categories->isNotEmpty())
                    <div class="mt-6 flex max-w-2xl flex-wrap items-center justify-center gap-2" data-reveal>
                        <a href="{{ $categoryUrl(null) }}" @class([
                            'rounded-full px-3.5 py-1.5 text-xs font-medium transition',
                            'bg-white text-ink' => $filters->kategori === null,
                            'border border-white/15 bg-white/5 text-white/70 hover:text-white' => $filters->kategori !== null,
                        ])>Tümü</a>
                        @foreach ($categories as $category)
                            <a href="{{ $categoryUrl($category->slug) }}" @class([
                                'rounded-full px-3.5 py-1.5 text-xs font-medium transition',
                                'bg-white text-ink' => $filters->kategori === $category->slug,
                                'border border-white/15 bg-white/5 text-white/70 hover:text-white' => $filters->kategori !== $category->slug,
                            ])>{{ $category->name }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        {{-- ================= FİLTRE BARI ================= --}}
        <form method="get" action="{{ route('sites.index') }}" class="rounded-[20px] border border-ink/10 bg-paper p-4 sm:p-5" data-reveal>
            <input type="hidden" name="kategori" value="{{ $filters->kategori }}">
            <input type="hidden" name="q" value="{{ $filters->q }}">

            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[110px] flex-1">
                    <label for="fiyat_min" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Fiyat min</label>
                    <input id="fiyat_min" type="number" name="fiyat_min" min="0" step="1" value="{{ $filters->fiyatMin }}" placeholder="0" class="w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink placeholder:text-ink-3 focus:border-ink/30 focus:ring-0">
                </div>
                <div class="min-w-[110px] flex-1">
                    <label for="fiyat_max" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Fiyat max</label>
                    <input id="fiyat_max" type="number" name="fiyat_max" min="0" step="1" value="{{ $filters->fiyatMax }}" placeholder="—" class="w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink placeholder:text-ink-3 focus:border-ink/30 focus:ring-0">
                </div>
                <div class="min-w-[90px] flex-1">
                    <label for="da_min" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">DA min</label>
                    <input id="da_min" type="number" name="da_min" min="0" step="1" value="{{ $filters->daMin }}" placeholder="0" class="w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink placeholder:text-ink-3 focus:border-ink/30 focus:ring-0">
                </div>
                <div class="min-w-[90px] flex-1">
                    <label for="da_max" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">DA max</label>
                    <input id="da_max" type="number" name="da_max" min="0" step="1" value="{{ $filters->daMax }}" placeholder="—" class="w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink placeholder:text-ink-3 focus:border-ink/30 focus:ring-0">
                </div>
                <div class="min-w-[150px] flex-1">
                    <label for="sort" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Sıralama</label>
                    <select id="sort" name="sort" class="w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm font-medium text-ink focus:border-ink/30 focus:ring-0">
                        @foreach ($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters->sort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]">
                        Filtrele
                    </button>
                    <a href="{{ route('sites.index') }}" class="inline-flex items-center gap-x-1.5 rounded-xl border border-ink/10 bg-white px-4 py-2.5 text-sm font-medium text-ink-2 transition hover:border-ink/25 hover:text-ink">
                        Sıfırla
                    </a>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-ink/10 pt-4">
                <label class="cursor-pointer">
                    <input type="checkbox" name="dofollow" value="1" @checked($filters->dofollowOnly) class="peer sr-only" onchange="this.form.submit()">
                    <span class="inline-flex items-center gap-x-1.5 rounded-full border border-ink/10 bg-white px-3.5 py-1.5 text-xs font-medium text-ink-2 transition peer-checked:border-transparent peer-checked:bg-ink peer-checked:text-white">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        Sadece Dofollow
                    </span>
                </label>
                <label class="cursor-pointer">
                    <input type="checkbox" name="news" value="1" @checked($filters->newsApprovedOnly) class="peer sr-only" onchange="this.form.submit()">
                    <span class="inline-flex items-center gap-x-1.5 rounded-full border border-ink/10 bg-white px-3.5 py-1.5 text-xs font-medium text-ink-2 transition peer-checked:border-transparent peer-checked:bg-ink peer-checked:text-white">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        Yalnızca News Onaylı
                    </span>
                </label>

                <span class="ms-auto text-sm font-medium text-ink-2">
                    <strong class="font-display text-ink">{{ $fmt($sites->total()) }}</strong> site listeleniyor
                </span>
            </div>
        </form>

        {{-- ================= TABLO ================= --}}
        <div class="mt-6" data-reveal>
            @if ($sites->isEmpty())
                <div class="rounded-[20px] border border-ink/10 bg-paper px-6 py-16 text-center">
                    <p class="font-display text-lg font-semibold text-ink">Bu filtrelere uygun aktif site bulunamadı.</p>
                    <p class="mt-1.5 text-sm text-ink-2">Filtreleri gevşetin ya da tüm katalogu görüntülemek için sıfırlayın.</p>
                    <a href="{{ route('sites.index') }}" class="mt-5 inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white">
                        Filtreleri Sıfırla
                    </a>
                </div>
            @else
                <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[880px] border-collapse text-start">
                            <thead>
                                <tr class="border-b border-ink/10 bg-paper">
                                    <th class="sticky start-0 z-10 bg-paper px-5 py-3.5 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Site</th>
                                    <th class="px-4 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3">Link Tipi</th>
                                    <th class="px-4 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3">News</th>
                                    <th class="px-4 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3">DA</th>
                                    <th class="px-4 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3">PA</th>
                                    <th class="px-4 py-3.5 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Fiyat</th>
                                    <th class="px-5 py-3.5 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Aksiyon</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink/5">
                                @foreach ($sites as $site)
                                    @php
                                        $currency = $site->currency?->value ?? (string) $site->currency;
                                        $hasDiscount = $site->discount_price !== null && (float) $site->discount_price < (float) $site->price;
                                    @endphp
                                    <tr class="group transition hover:bg-paper">
                                        <td class="sticky start-0 z-10 bg-white px-5 py-3.5 transition group-hover:bg-paper">
                                            <a href="{{ route('sites.show', $site->domain) }}" class="flex items-center gap-x-3">
                                                <x-site-favicon :domain="$site->domain" :size="28" class="shrink-0 rounded-lg" />
                                                <span class="min-w-0">
                                                    <span class="block truncate text-sm font-semibold text-ink transition group-hover:text-accent-700">{{ $site->domain }}</span>
                                                    <span class="block truncate text-xs text-ink-3">{{ $site->category?->name ?? 'Kategorisiz' }}</span>
                                                </span>
                                            </a>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            @if ($site->is_dofollow)
                                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Dofollow</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-ink/5 px-2.5 py-1 text-[11px] font-semibold text-ink-3">Nofollow</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            @if ($site->is_news_approved)
                                                <span class="inline-flex items-center rounded-full bg-accent-100 px-2.5 py-1 text-[11px] font-semibold text-accent-700">Var</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-ink/5 px-2.5 py-1 text-[11px] font-semibold text-ink-3">Yok</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            @if ($site->da_value !== null)
                                                <span class="inline-flex min-w-9 items-center justify-center rounded-lg bg-paper px-2 py-1 text-xs font-bold text-ink">{{ number_format((float) $site->da_value, 0) }}</span>
                                            @else
                                                <span class="text-xs text-ink-3">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            @if ($site->pa_value !== null)
                                                <span class="inline-flex min-w-9 items-center justify-center rounded-lg bg-paper px-2 py-1 text-xs font-bold text-ink">{{ number_format((float) $site->pa_value, 0) }}</span>
                                            @else
                                                <span class="text-xs text-ink-3">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-end">
                                            @if ($hasDiscount)
                                                <span class="block text-[11px] text-ink-3 line-through">{{ $money((float) $site->price, $currency) }}</span>
                                                <span class="font-display text-sm font-bold text-accent-600">{{ $money((float) $site->discount_price, $currency) }}</span>
                                            @else
                                                <span class="font-display text-sm font-bold text-ink">{{ $money((float) $site->price, $currency) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5 text-end">
                                            <form method="post" action="{{ route('cart.add') }}">
                                                @csrf
                                                <input type="hidden" name="product_type" value="site_article">
                                                <input type="hidden" name="site_id" value="{{ $site->id }}">
                                                @guest
                                                    <button
                                                        type="button"
                                                        class="group inline-flex items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-3.5 py-2 text-xs font-semibold text-white transition hover:scale-[1.04] active:scale-[0.98]"
                                                        onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))"
                                                    >
                                                        <svg class="size-3.5 transition group-hover:rotate-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                        Sepete Ekle
                                                    </button>
                                                @else
                                                    <button
                                                        type="submit"
                                                        class="group inline-flex items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-3.5 py-2 text-xs font-semibold text-white transition hover:scale-[1.04] active:scale-[0.98]"
                                                    >
                                                        <svg class="size-3.5 transition group-hover:rotate-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                        Sepete Ekle
                                                    </button>
                                                @endguest
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    {{ $sites->links('vendor.pagination.storefront') }}
                </div>
            @endif
        </div>
    </div>
@endsection
