@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator<\App\Models\Site> $sites */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SiteCategory> $categories */
    /** @var \Illuminate\Support\Collection<int, \App\Models\FooterLinkDurationOption> $durationOptions */
    $money = fn (float $amount, string $currency): string => number_format($amount, 2, ',', '.').($currency === 'TRY' ? '₺' : '$');
    $num = fn (?float $value): string => $value !== null ? number_format($value, 0, ',', '.') : '—';

    $categoryUrl = fn (?string $slug) => route('footer-links.index', array_filter([
        'q' => $q,
        'sort' => $sort,
        'kategori' => $slug,
    ]));
@endphp

@section('content')
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-light relative overflow-hidden rounded-3xl text-ink">
            <div class="relative mx-auto flex max-w-7xl flex-col items-center px-5 pb-10 pt-14 text-center sm:px-8 sm:pb-12 sm:pt-16">
                <p class="inline-flex items-center gap-x-2 rounded-full border border-ink/10 bg-white py-1 pe-3.5 ps-1 text-xs text-ink-2 shadow-soft">
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Footer Link</span>
                    {{ $sites->total() }}+ site
                </p>
                <h1 class="mt-5 max-w-2xl font-display text-4xl font-medium leading-[1.12] sm:text-5xl">
                    Kalıcı veya süreli footer link yerleşimi alın
                </h1>
                <p class="mt-4 max-w-xl text-lg font-medium leading-relaxed text-ink-2">
                    Site ve süre seçin, sepete ekleyin — footer linkiniz seçtiğiniz süre boyunca yayında kalsın.
                </p>

                <form method="get" action="{{ route('footer-links.index') }}" class="mt-7 flex w-full max-w-xl items-center gap-2 rounded-full border border-ink/10 bg-white p-1.5 shadow-pop" role="search">
                    <input type="hidden" name="kategori" value="{{ $kategori }}">
                    <svg class="ms-3 size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <input type="search" name="q" value="{{ $q }}" placeholder="Örn. habergazetesi.com.tr" class="w-full border-0 bg-transparent p-0 py-2 text-sm text-ink placeholder:text-ink-3 focus:ring-0" aria-label="Site ara">
                    <button type="submit" class="inline-flex shrink-0 items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.03] active:scale-[0.98]">
                        Ara
                    </button>
                </form>

                @if ($categories->isNotEmpty())
                    <div class="mt-6 flex max-w-2xl flex-wrap items-center justify-center gap-2">
                        <a href="{{ $categoryUrl(null) }}" @class(['rounded-full px-3.5 py-1.5 text-xs font-medium transition', 'bg-white text-ink' => $kategori === null, 'border border-ink/10 bg-white text-ink-2 hover:text-ink' => $kategori !== null])>Tümü</a>
                        @foreach ($categories as $category)
                            <a href="{{ $categoryUrl($category->slug) }}" @class(['rounded-full px-3.5 py-1.5 text-xs font-medium transition', 'bg-white text-ink' => $kategori === $category->slug, 'border border-ink/10 bg-white text-ink-2 hover:text-ink' => $kategori !== $category->slug])>{{ $category->name }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if ($sites->isEmpty())
            <div class="rounded-[20px] border border-ink/10 bg-paper px-6 py-16 text-center">
                <p class="font-display text-lg font-semibold text-ink">Bu filtrelere uygun site bulunamadı.</p>
                <a href="{{ route('footer-links.index') }}" class="mt-5 inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white">Filtreleri Sıfırla</a>
            </div>
        @else
            <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1080px] border-collapse text-start" x-data="{ openRows: {} }">
                        <thead>
                            <tr class="divide-x divide-ink/10 border-b border-ink/10 bg-paper">
                                <th class="sticky start-0 z-10 bg-paper px-5 py-3.5 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Site Adı</th>
                                <th class="px-3 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3">
                                    <span class="inline-flex items-center gap-1"><x-metric-icon source="google" />Index</span>
                                </th>
                                <th class="px-3 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3">
                                    <span class="inline-flex items-center gap-1"><x-metric-icon source="google" />News</span>
                                </th>
                                <th class="px-3 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3">Site Yaşı</th>
                                <th class="px-3 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3">
                                    <span class="inline-flex items-center gap-1"><x-metric-icon source="moz" />DA</span>
                                </th>
                                <th class="px-3 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3">
                                    <span class="inline-flex items-center gap-1"><x-metric-icon source="moz" />PA</span>
                                </th>
                                <th class="px-3 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3">Link Türü</th>
                                <th class="px-4 py-3.5 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Süre</th>
                                <th class="px-4 py-3.5 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Fiyat</th>
                                <th class="px-5 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/5">
                            @foreach ($sites as $site)
                                    @php
                                        $optionPrices = $durationOptions->mapWithKeys(
                                            fn ($option) => [(string) $option->id => $money((float) $site->base_price, $site->currency?->value ?? 'TRY')]
                                        );
                                        $isFavorited = in_array($site->id, $favoritedSiteIds, true);

                                        $metrics = [
                                            ['label' => 'Aylık Trafik', 'value' => $num($site->monthly_traffic_value !== null ? (float) $site->monthly_traffic_value : null), 'icon' => 'google'],
                                            ['label' => 'Moz Rank', 'value' => $num($site->moz_rank_value !== null ? (float) $site->moz_rank_value : null), 'icon' => 'moz'],
                                            ['label' => 'Majestic CF', 'value' => $num($site->majestic_cf_value !== null ? (float) $site->majestic_cf_value : null), 'icon' => 'majestic'],
                                            ['label' => 'Majestic TF', 'value' => $num($site->majestic_tf_value !== null ? (float) $site->majestic_tf_value : null), 'icon' => 'majestic'],
                                            ['label' => 'Backlink', 'value' => $num($site->backlinks_value !== null ? (float) $site->backlinks_value : null), 'icon' => 'ahrefs'],
                                            ['label' => 'Link Çıkışı', 'value' => $site->max_link_count !== null ? (string) $site->max_link_count : '—', 'icon' => null],
                                            ['label' => 'Spam Score', 'value' => $num($site->spam_score_value !== null ? (float) $site->spam_score_value : null), 'icon' => 'moz'],
                                            ['label' => 'Ahrefs Kelime', 'value' => $num($site->ahrefs_keywords_value !== null ? (float) $site->ahrefs_keywords_value : null), 'icon' => 'ahrefs'],
                                            ['label' => 'Ahrefs DR', 'value' => $num($site->ahrefs_dr_value !== null ? (float) $site->ahrefs_dr_value : null), 'icon' => 'ahrefs'],
                                            ['label' => 'Semrush AS', 'value' => $num($site->semrush_authority_score_value !== null ? (float) $site->semrush_authority_score_value : null), 'icon' => 'semrush'],
                                        ];
                                    @endphp
                                    <tr
                                        class="group transition hover:bg-paper"
                                        x-data="{ optionId: '{{ $durationOptions->first()?->id }}', prices: {{ $optionPrices->toJson() }} }"
                                    >
                                        <td class="sticky start-0 z-10 bg-white px-5 py-3.5 transition group-hover:bg-paper">
                                            <x-site-identity :site="$site" :height="28" />
                                        </td>
                                        <td class="px-3 py-3.5 text-center">
                                            @if ($site->is_google_indexed)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-1 text-[11px] font-semibold text-emerald-700"><x-metric-icon source="google" />Var</span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-ink/5 px-2 py-1 text-[11px] font-semibold text-ink-3"><x-metric-icon source="google" />Yok</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3.5 text-center">
                                            @if ($site->is_news_approved)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-accent-100 px-2 py-1 text-[11px] font-semibold text-accent-700"><x-metric-icon source="google" />Var</span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-ink/5 px-2 py-1 text-[11px] font-semibold text-ink-3"><x-metric-icon source="google" />Yok</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3.5 text-center">
                                            <span class="text-xs font-medium text-ink-2">{{ $site->age !== null ? $site->age.' yıl' : '—' }}</span>
                                        </td>
                                        <td class="px-3 py-3.5 text-center">
                                            <span class="inline-flex items-center gap-1">
                                                <x-metric-icon source="moz" />
                                                <span class="text-xs font-bold text-ink">{{ $num($site->da_value !== null ? (float) $site->da_value : null) }}</span>
                                            </span>
                                        </td>
                                        <td class="px-3 py-3.5 text-center">
                                            <span class="inline-flex items-center gap-1">
                                                <x-metric-icon source="moz" />
                                                <span class="text-xs font-bold text-ink">{{ $num($site->pa_value !== null ? (float) $site->pa_value : null) }}</span>
                                            </span>
                                        </td>
                                        <td class="px-3 py-3.5 text-center">
                                            @if ($site->is_dofollow)
                                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Dofollow</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-ink/5 px-2.5 py-1 text-[11px] font-semibold text-ink-3">Nofollow</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <select x-model="optionId" class="rounded-xl border border-ink/10 bg-white px-3 py-2 text-sm text-ink focus:border-ink/30 focus:ring-0">
                                                @foreach ($durationOptions as $option)
                                                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-3.5 text-end">
                                            <span class="font-display text-sm font-bold text-ink" x-text="prices[optionId]"></span>
                                        </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                type="button"
                                                @click="openRows[{{ $site->id }}] = !openRows[{{ $site->id }}]"
                                                class="inline-flex size-8 shrink-0 items-center justify-center rounded-full border border-ink/10 text-ink-2 transition hover:border-ink/25 hover:text-ink"
                                                :aria-expanded="(!!openRows[{{ $site->id }}]).toString()"
                                                aria-label="Site detaylarını göster"
                                            >
                                                <svg class="size-4 shrink-0 transition" :class="openRows[{{ $site->id }}] ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                            </button>

                                            <form method="post" action="{{ route('cart.add') }}">
                                                @csrf
                                                <input type="hidden" name="product_type" value="footer_link">
                                                <input type="hidden" name="site_id" value="{{ $site->id }}">
                                                <input type="hidden" name="footer_link_duration_option_id" :value="optionId">
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

                                            <form method="post" action="{{ route('sites.favorite', $site) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="inline-flex size-8 shrink-0 items-center justify-center rounded-full border border-ink/10 transition hover:border-ink/25"
                                                    aria-label="{{ $isFavorited ? 'Favorilerden çıkar' : 'Favorilere ekle' }}"
                                                >
                                                    <svg class="size-4 {{ $isFavorited ? 'text-brand-500' : 'text-ink-3' }}" viewBox="0 0 24 24" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <tr x-show="openRows[{{ $site->id }}]" x-cloak>
                                    <td colspan="10" class="bg-paper px-5 py-4">
                                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
                                            @foreach ($metrics as $metric)
                                                <div class="rounded-xl bg-white px-3 py-2 shadow-soft">
                                                    <p class="flex items-center gap-1 truncate text-[10px] font-semibold uppercase tracking-wide text-ink-3">
                                                        @if ($metric['icon'])
                                                            <x-metric-icon :source="$metric['icon']" />
                                                        @endif
                                                        {{ $metric['label'] }}
                                                    </p>
                                                    <p class="mt-0.5 truncate text-sm font-bold text-ink">{{ $metric['value'] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
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
@endsection
