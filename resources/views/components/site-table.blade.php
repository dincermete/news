@props([
    'sites',
    'favoritedSiteIds' => [],
    'productType' => 'site_article',
    'price' => null,
    /**
     * Visible columns. Null keeps the full catalog set.
     * Product detail typically passes: domain, indexed, price, actions.
     *
     * @var list<string>|null
     */
    'columns' => null,
])
@php
    /** @var \Closure(\App\Models\Site): float|null $price */
    $money = fn (float $amount, string $currency): string => number_format($amount, 2, ',', '.').($currency === 'TRY' ? '₺' : '$');
    $num = fn (?float $value): string => $value !== null ? number_format($value, 0, ',', '.') : '—';
    $thMetric = 'site-catalog-table__th relative whitespace-nowrap px-3 py-3.5 pe-5 text-start align-bottom text-[11px] font-semibold tracking-wide text-ink';
    $tdMetric = 'site-catalog-table__td whitespace-nowrap px-3 py-3.5 text-center';
    $sortBtn = 'group/sort relative block w-full rounded-md text-start transition hover:text-accent-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/40';
    $sortIcon = 'site-catalog-table__sort-icon pointer-events-none absolute end-0 bottom-0 size-3 transition duration-200';

    $allColumns = ['domain', 'indexed', 'news', 'da', 'pa', 'dr', 'semrush', 'age', 'dofollow', 'price', 'actions'];
    $visibleColumns = collect($columns ?? $allColumns)->intersect($allColumns)->values();
    $show = fn (string $key): bool => $visibleColumns->contains($key);
    $isLimited = $columns !== null;
    $colCount = max(1, $visibleColumns->count());

    // Catalog keeps dense metrics behind sm:. Product-detail limited tables still
    // hide price/actions below sm so mobile keeps site adı + Google Index only.
    $desktopOnly = ' hidden sm:table-cell';
    $tableMinWidth = $isLimited ? 'sm:min-w-[640px]' : 'sm:min-w-[980px]';
@endphp
<div class="overflow-x-auto" x-data="siteTable()">
    <table
        x-ref="table"
        class="site-catalog-table w-full min-w-0 text-start {{ $tableMinWidth }}"
        :class="sorting && 'site-catalog-table--sorting'"
    >
        <thead>
            <tr class="site-catalog-table__head">
                @if ($show('domain'))
                    <th class="site-catalog-table__th sticky start-0 z-10 min-w-[220px] px-5 py-3.5 pe-8 text-start align-bottom text-[11px] font-semibold uppercase tracking-wide text-ink">
                        <button type="button" class="{{ $sortBtn }}" @click="sortBy('domain')" :aria-sort="sortKey === 'domain' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'">
                            <span>Site Adı</span>
                            <svg class="{{ $sortIcon }}" :class="sortIconClass('domain')" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                        </button>
                    </th>
                @endif
                @if ($show('indexed'))
                    <th class="{{ $thMetric }}">
                        <button type="button" class="{{ $sortBtn }}" @click="sortBy('indexed')" :aria-sort="sortKey === 'indexed' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'">
                            <x-metric-th-label logo="google.svg" brand="Google" metric="Index" />
                            <svg class="{{ $sortIcon }}" :class="sortIconClass('indexed')" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                        </button>
                    </th>
                @endif
                @if ($show('news'))
                    <th class="{{ $thMetric }}{{ $desktopOnly }}">
                        <button type="button" class="{{ $sortBtn }}" @click="sortBy('news')" :aria-sort="sortKey === 'news' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'">
                            <x-metric-th-label logo="google-news.svg" brand="Google" metric="News" />
                            <svg class="{{ $sortIcon }}" :class="sortIconClass('news')" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                        </button>
                    </th>
                @endif
                @if ($show('da'))
                    <th class="{{ $thMetric }}{{ $desktopOnly }}">
                        <button type="button" class="{{ $sortBtn }}" @click="sortBy('da')" :aria-sort="sortKey === 'da' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'">
                            <x-metric-th-label logo="moz.svg" brand="Moz" metric="DA" />
                            <svg class="{{ $sortIcon }}" :class="sortIconClass('da')" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                        </button>
                    </th>
                @endif
                @if ($show('pa'))
                    <th class="{{ $thMetric }}{{ $desktopOnly }}">
                        <button type="button" class="{{ $sortBtn }}" @click="sortBy('pa')" :aria-sort="sortKey === 'pa' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'">
                            <x-metric-th-label logo="moz.svg" brand="Moz" metric="PA" />
                            <svg class="{{ $sortIcon }}" :class="sortIconClass('pa')" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                        </button>
                    </th>
                @endif
                @if ($show('dr'))
                    <th class="{{ $thMetric }}{{ $desktopOnly }}">
                        <button type="button" class="{{ $sortBtn }}" @click="sortBy('dr')" :aria-sort="sortKey === 'dr' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'">
                            <x-metric-th-label logo="ahrefs.svg" brand="Ahrefs" metric="DR" />
                            <svg class="{{ $sortIcon }}" :class="sortIconClass('dr')" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                        </button>
                    </th>
                @endif
                @if ($show('semrush'))
                    <th class="{{ $thMetric }}{{ $desktopOnly }}">
                        <button type="button" class="{{ $sortBtn }}" @click="sortBy('semrush')" :aria-sort="sortKey === 'semrush' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'">
                            <x-metric-th-label logo="semrush.svg" brand="Semrush" metric="AS" />
                            <svg class="{{ $sortIcon }}" :class="sortIconClass('semrush')" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                        </button>
                    </th>
                @endif
                @if ($show('age'))
                    <th class="{{ $thMetric }}{{ $desktopOnly }}">
                        <button type="button" class="{{ $sortBtn }}" @click="sortBy('age')" :aria-sort="sortKey === 'age' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'">
                            <span class="uppercase">Site Yaşı</span>
                            <svg class="{{ $sortIcon }}" :class="sortIconClass('age')" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                        </button>
                    </th>
                @endif
                @if ($show('dofollow'))
                    <th class="{{ $thMetric }}{{ $desktopOnly }}">
                        <button type="button" class="{{ $sortBtn }}" @click="sortBy('dofollow')" :aria-sort="sortKey === 'dofollow' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'">
                            <span class="uppercase">Link Türü</span>
                            <svg class="{{ $sortIcon }}" :class="sortIconClass('dofollow')" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                        </button>
                    </th>
                @endif
                @if ($show('price'))
                    <th class="site-catalog-table__th relative whitespace-nowrap px-4 py-3.5 pe-7 text-end align-bottom text-[11px] font-semibold uppercase tracking-wide text-ink{{ $desktopOnly }}">
                        <button type="button" class="{{ $sortBtn }} text-end" @click="sortBy('price')" :aria-sort="sortKey === 'price' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'">
                            <span>Fiyat</span>
                            <svg class="{{ $sortIcon }}" :class="sortIconClass('price')" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                        </button>
                    </th>
                @endif
                @if ($show('actions'))
                    <th class="site-catalog-table__th whitespace-nowrap px-4 py-3.5 text-end align-bottom text-[11px] font-semibold uppercase tracking-wide text-ink{{ $desktopOnly }}">İşlem</th>
                @endif
            </tr>
            <tr class="site-catalog-table__gap" aria-hidden="true">
                <td colspan="{{ $colCount }}"></td>
            </tr>
        </thead>
        @foreach ($sites as $site)
            @php
                $currency = $site->currency?->value ?? (string) $site->currency;
                $hasDiscount = $price === null && $site->discount_price !== null && (float) $site->discount_price < (float) $site->price;
                $isFavorited = in_array($site->id, $favoritedSiteIds, true);
                $singlePrice = $price !== null ? $price($site) : null;
                $sortPrice = $hasDiscount
                    ? (float) $site->discount_price
                    : (float) ($singlePrice ?? $site->price);

                $metrics = [
                    ['label' => 'Aylık Trafik', 'value' => $num($site->monthly_traffic_value !== null ? (float) $site->monthly_traffic_value : null), 'icon' => 'google'],
                    ['label' => 'Moz Rank', 'value' => $num($site->moz_rank_value !== null ? (float) $site->moz_rank_value : null), 'icon' => 'moz'],
                    ['label' => 'Majestic CF', 'value' => $num($site->majestic_cf_value !== null ? (float) $site->majestic_cf_value : null), 'icon' => 'majestic'],
                    ['label' => 'Majestic TF', 'value' => $num($site->majestic_tf_value !== null ? (float) $site->majestic_tf_value : null), 'icon' => 'majestic'],
                    ['label' => 'Backlink', 'value' => $num($site->backlinks_value !== null ? (float) $site->backlinks_value : null), 'icon' => 'ahrefs'],
                    ['label' => 'Link Çıkışı', 'value' => $site->max_link_count !== null ? (string) $site->max_link_count : '—', 'icon' => null],
                    ['label' => 'Spam Score', 'value' => $num($site->spam_score_value !== null ? (float) $site->spam_score_value : null), 'icon' => 'moz'],
                    ['label' => 'Ahrefs Kelime', 'value' => $num($site->ahrefs_keywords_value !== null ? (float) $site->ahrefs_keywords_value : null), 'icon' => 'ahrefs'],
                ];
            @endphp
            <tbody
                data-sort-group
                data-id="{{ $site->id }}"
                data-domain="{{ $site->domain }}"
                data-indexed="{{ $site->is_google_indexed ? '1' : '0' }}"
                data-news="{{ $site->is_news_approved ? '1' : '0' }}"
                data-da="{{ $site->da_value !== null ? (float) $site->da_value : '' }}"
                data-pa="{{ $site->pa_value !== null ? (float) $site->pa_value : '' }}"
                data-dr="{{ $site->ahrefs_dr_value !== null ? (float) $site->ahrefs_dr_value : '' }}"
                data-semrush="{{ $site->semrush_authority_score_value !== null ? (float) $site->semrush_authority_score_value : '' }}"
                data-age="{{ $site->age !== null ? $site->age : '' }}"
                data-dofollow="{{ $site->is_dofollow ? '1' : '0' }}"
                data-price="{{ $sortPrice }}"
            >
                <tr
                    class="site-catalog-table__row group cursor-pointer transition hover:bg-paper"
                    :class="{
                        'site-catalog-table__row--first': isFirst({{ $site->id }}),
                        'site-catalog-table__row--end': !openRows[{{ $site->id }}] && isLast({{ $site->id }}),
                    }"
                    @click="window.location = '{{ storefront_site_url($site) }}'"
                >
                    @if ($show('domain'))
                        <td class="site-catalog-table__td sticky start-0 z-10 min-w-[220px] bg-white px-5 py-3.5 transition group-hover:bg-paper">
                            <x-site-identity :site="$site" :height="28" :label="$site->domain" stop-propagation />
                        </td>
                    @endif
                    @if ($show('indexed'))
                        <td class="{{ $tdMetric }}">
                            @if ($site->is_google_indexed)
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-1 text-[11px] font-semibold text-emerald-700">Var</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-ink/5 px-2 py-1 text-[11px] font-semibold text-ink-3">Yok</span>
                            @endif
                        </td>
                    @endif
                    @if ($show('news'))
                        <td class="{{ $tdMetric }}{{ $desktopOnly }}">
                            @if ($site->is_news_approved)
                                <span class="inline-flex items-center rounded-full bg-accent-100 px-2 py-1 text-[11px] font-semibold text-accent-700">Var</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-ink/5 px-2 py-1 text-[11px] font-semibold text-ink-3">Yok</span>
                            @endif
                        </td>
                    @endif
                    @if ($show('da'))
                        <td class="{{ $tdMetric }}{{ $desktopOnly }}">
                            <span class="text-xs font-bold text-ink">{{ $num($site->da_value !== null ? (float) $site->da_value : null) }}</span>
                        </td>
                    @endif
                    @if ($show('pa'))
                        <td class="{{ $tdMetric }}{{ $desktopOnly }}">
                            <span class="text-xs font-bold text-ink">{{ $num($site->pa_value !== null ? (float) $site->pa_value : null) }}</span>
                        </td>
                    @endif
                    @if ($show('dr'))
                        <td class="{{ $tdMetric }}{{ $desktopOnly }}">
                            <span class="text-xs font-bold text-ink">{{ $num($site->ahrefs_dr_value !== null ? (float) $site->ahrefs_dr_value : null) }}</span>
                        </td>
                    @endif
                    @if ($show('semrush'))
                        <td class="{{ $tdMetric }}{{ $desktopOnly }}">
                            <span class="text-xs font-bold text-ink">{{ $num($site->semrush_authority_score_value !== null ? (float) $site->semrush_authority_score_value : null) }}</span>
                        </td>
                    @endif
                    @if ($show('age'))
                        <td class="{{ $tdMetric }}{{ $desktopOnly }}">
                            <span class="text-xs font-medium text-ink-2">{{ $site->age !== null ? $site->age.' yıl' : '—' }}</span>
                        </td>
                    @endif
                    @if ($show('dofollow'))
                        <td class="{{ $tdMetric }}{{ $desktopOnly }}">
                            @if ($site->is_dofollow)
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Dofollow</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-ink/5 px-2.5 py-1 text-[11px] font-semibold text-ink-3">Nofollow</span>
                            @endif
                        </td>
                    @endif
                    @if ($show('price'))
                        <td class="site-catalog-table__td whitespace-nowrap px-4 py-3.5 text-end{{ $desktopOnly }}">
                            @if ($hasDiscount)
                                <span class="block text-[11px] text-ink-3 line-through">{{ $money((float) $site->price, $currency) }}</span>
                                <span class="font-display text-sm font-bold tabular-nums text-accent-600">{{ $money((float) $site->discount_price, $currency) }}</span>
                            @else
                                <span class="font-display text-sm font-bold tabular-nums text-ink">{{ $money($singlePrice ?? (float) $site->price, $currency) }}</span>
                            @endif
                        </td>
                    @endif
                    @if ($show('actions'))
                        <td class="site-catalog-table__td whitespace-nowrap px-4 py-3.5{{ $desktopOnly }}">
                            <div class="flex items-center justify-end gap-1.5">
                                @unless ($isLimited)
                                    <button
                                        type="button"
                                        @click.stop="openRows[{{ $site->id }}] = !openRows[{{ $site->id }}]"
                                        class="inline-flex size-8 shrink-0 items-center justify-center rounded-full border border-ink/10 text-ink-2 transition hover:border-ink/25 hover:text-ink"
                                        :aria-expanded="(!!openRows[{{ $site->id }}]).toString()"
                                        aria-label="Site detaylarını göster"
                                    >
                                        <svg class="size-4 shrink-0 transition" :class="openRows[{{ $site->id }}] ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                    </button>
                                @endunless

                                <form method="post" action="{{ route('cart.add') }}" @click.stop>
                                    @csrf
                                    <input type="hidden" name="product_type" value="{{ $productType }}">
                                    <input type="hidden" name="site_id" value="{{ $site->id }}">
                                    @guest
                                        <button
                                            type="button"
                                            class="inline-flex size-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-b from-black to-[#363b3c] text-white transition hover:scale-[1.04] active:scale-[0.98]"
                                            aria-label="Sepete ekle"
                                            onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))"
                                        >
                                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                        </button>
                                    @else
                                        <button
                                            type="submit"
                                            class="inline-flex size-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-b from-black to-[#363b3c] text-white transition hover:scale-[1.04] active:scale-[0.98]"
                                            aria-label="Sepete ekle"
                                        >
                                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                        </button>
                                    @endguest
                                </form>

                                <form method="post" action="{{ route('sites.favorite', $site) }}" @click.stop>
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
                    @endif
                </tr>
                @unless ($isLimited)
                    <tr
                        class="site-catalog-table__detail"
                        x-show="openRows[{{ $site->id }}]"
                        x-cloak
                        :class="{ 'site-catalog-table__detail--end': isLast({{ $site->id }}) }"
                    >
                        <td colspan="{{ $colCount }}" class="site-catalog-table__detail-cell px-5 py-4">
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                                @foreach ($metrics as $metric)
                                    <div class="rounded-lg border border-accent-100 bg-white px-3 py-2">
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
                @endunless
            </tbody>
        @endforeach
    </table>
</div>
