@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator<\App\Models\Site> $sites */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SiteCategory> $categories */

    $money = function (float $amount, string $currency): string {
        $symbol = $currency === 'TRY' ? '₺' : '$';

        return number_format($amount, 2, ',', '.').$symbol;
    };

    $categoryUrl = fn (?string $slug) => route('press-release.index', array_filter([
        'q' => $q,
        'sort' => $sort,
        'kategori' => $slug,
    ]));
@endphp

@section('content')
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto flex max-w-6xl flex-col items-center px-5 pb-10 pt-14 text-center sm:px-8 sm:pb-12 sm:pt-16">
                <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80">
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Basın Bülteni</span>
                    {{ $sites->total() }}+ site
                </p>
                <h1 class="mt-5 max-w-2xl font-display text-4xl font-medium leading-[1.12] sm:text-5xl">
                    Basın bülteninizi haber sitelerinde yayınlayın
                </h1>
                <p class="mt-4 max-w-xl text-lg font-medium leading-relaxed text-white/65">
                    Sitenizi seçin, sepete ekleyin, içeriğinizi yükleyin — kalanını biz hallederiz.
                </p>

                <form method="get" action="{{ route('press-release.index') }}" class="mt-7 flex w-full max-w-xl items-center gap-2 rounded-full border border-white/15 bg-white p-1.5 shadow-pop" role="search">
                    <input type="hidden" name="kategori" value="{{ $kategori }}">
                    <svg class="ms-3 size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <input type="search" name="q" value="{{ $q }}" placeholder="Örn. habergazetesi.com.tr" class="w-full border-0 bg-transparent p-0 py-2 text-sm text-ink placeholder:text-ink-3 focus:ring-0" aria-label="Site ara">
                    <button type="submit" class="inline-flex shrink-0 items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.03] active:scale-[0.98]">
                        Ara
                    </button>
                </form>

                @if ($categories->isNotEmpty())
                    <div class="mt-6 flex max-w-2xl flex-wrap items-center justify-center gap-2">
                        <a href="{{ $categoryUrl(null) }}" @class(['rounded-full px-3.5 py-1.5 text-xs font-medium transition', 'bg-white text-ink' => $kategori === null, 'border border-white/15 bg-white/5 text-white/70 hover:text-white' => $kategori !== null])>Tümü</a>
                        @foreach ($categories as $category)
                            <a href="{{ $categoryUrl($category->slug) }}" @class(['rounded-full px-3.5 py-1.5 text-xs font-medium transition', 'bg-white text-ink' => $kategori === $category->slug, 'border border-white/15 bg-white/5 text-white/70 hover:text-white' => $kategori !== $category->slug])>{{ $category->name }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-[20px] border border-ink/10 bg-paper p-4 sm:p-5">
            <form method="get" action="{{ route('press-release.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="q" value="{{ $q }}">
                <input type="hidden" name="kategori" value="{{ $kategori }}">
                <select name="sort" onchange="this.form.submit()" class="rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm font-medium text-ink focus:border-ink/30 focus:ring-0">
                    <option value="price_asc" @selected($sort === 'price_asc')>Fiyat: Artan</option>
                    <option value="price_desc" @selected($sort === 'price_desc')>Fiyat: Azalan</option>
                    <option value="newest" @selected($sort === 'newest')>En Yeni</option>
                </select>
            </form>
            <span class="text-sm font-medium text-ink-2"><strong class="font-display text-ink">{{ $sites->total() }}</strong> site listeleniyor</span>
        </div>

        <div class="mt-6">
            @if ($sites->isEmpty())
                <div class="rounded-[20px] border border-ink/10 bg-paper px-6 py-16 text-center">
                    <p class="font-display text-lg font-semibold text-ink">Bu filtrelere uygun basın bülteni sitesi bulunamadı.</p>
                    <a href="{{ route('press-release.index') }}" class="mt-5 inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white">Filtreleri Sıfırla</a>
                </div>
            @else
                <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[620px] border-collapse text-start">
                            <thead>
                                <tr class="border-b border-ink/10 bg-paper">
                                    <th class="sticky start-0 z-10 bg-paper px-5 py-3.5 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Site</th>
                                    <th class="px-4 py-3.5 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Fiyat</th>
                                    <th class="px-5 py-3.5 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Aksiyon</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink/5">
                                @foreach ($sites as $site)
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
                                        <td class="px-4 py-3.5 text-end">
                                            <span class="font-display text-sm font-bold text-ink">{{ $money((float) $site->press_release_price, $site->currency?->value ?? 'TRY') }}</span>
                                        </td>
                                        <td class="px-5 py-3.5 text-end">
                                            <form method="post" action="{{ route('cart.add') }}">
                                                @csrf
                                                <input type="hidden" name="product_type" value="press_release">
                                                <input type="hidden" name="site_id" value="{{ $site->id }}">
                                                @guest
                                                    <button type="button" class="group inline-flex items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-3.5 py-2 text-xs font-semibold text-white transition hover:scale-[1.04] active:scale-[0.98]" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">
                                                        Sepete Ekle
                                                    </button>
                                                @else
                                                    <button type="submit" class="group inline-flex items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-3.5 py-2 text-xs font-semibold text-white transition hover:scale-[1.04] active:scale-[0.98]">
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
