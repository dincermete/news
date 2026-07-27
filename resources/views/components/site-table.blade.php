@props([
    'sites',
    'favoritedSiteIds' => [],
    'productType' => 'site_article',
    'price' => null,
])
@php
    /** @var \Closure(\App\Models\Site): float|null $price */
    $money = fn (float $amount, string $currency): string => number_format($amount, 2, ',', '.').($currency === 'TRY' ? '₺' : '$');
    $num = fn (?float $value): string => $value !== null ? number_format($value, 0, ',', '.') : '—';
@endphp
<div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-0 border-collapse text-start sm:min-w-[980px]" x-data="{ openRows: {} }">
            <thead>
                <tr class="divide-x divide-ink/10 border-b border-ink/10 bg-paper">
                    <th class="sticky start-0 z-10 bg-paper px-5 py-3.5 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Site Adı</th>
                    <th class="px-3 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3">
                        <span class="inline-flex items-center gap-1"><x-metric-icon source="google" />Index</span>
                    </th>
                    <th class="hidden px-3 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3 sm:table-cell">
                        <span class="inline-flex items-center gap-1"><x-metric-icon source="google" />News</span>
                    </th>
                    <th class="hidden px-3 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3 sm:table-cell">Site Yaşı</th>
                    <th class="hidden px-3 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3 sm:table-cell">
                        <span class="inline-flex items-center gap-1"><x-metric-icon source="moz" />DA</span>
                    </th>
                    <th class="hidden px-3 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3 sm:table-cell">
                        <span class="inline-flex items-center gap-1"><x-metric-icon source="moz" />PA</span>
                    </th>
                    <th class="hidden px-3 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3 sm:table-cell">Link Türü</th>
                    <th class="hidden px-4 py-3.5 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3 sm:table-cell">Fiyat</th>
                    <th class="hidden px-5 py-3.5 sm:table-cell"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink/5">
                @foreach ($sites as $site)
                    @php
                        $currency = $site->currency?->value ?? (string) $site->currency;
                        $hasDiscount = $price === null && $site->discount_price !== null && (float) $site->discount_price < (float) $site->price;
                        $isFavorited = in_array($site->id, $favoritedSiteIds, true);
                        $singlePrice = $price !== null ? $price($site) : null;

                        $metrics = [
                            ['label' => 'Moz Rank', 'value' => $num($site->moz_rank_value !== null ? (float) $site->moz_rank_value : null), 'icon' => 'moz'],
                            ['label' => 'Majestic CF', 'value' => $num($site->majestic_cf_value !== null ? (float) $site->majestic_cf_value : null), 'icon' => 'majestic'],
                            ['label' => 'Majestic TF', 'value' => $num($site->majestic_tf_value !== null ? (float) $site->majestic_tf_value : null), 'icon' => 'majestic'],
                            ['label' => 'Ahrefs DR', 'value' => $num($site->ahrefs_dr_value !== null ? (float) $site->ahrefs_dr_value : null), 'icon' => 'ahrefs'],
                            ['label' => 'Ahrefs Rank', 'value' => $num($site->ahrefs_rank_value !== null ? (float) $site->ahrefs_rank_value : null), 'icon' => 'ahrefs'],
                            ['label' => 'Aylık Trafik', 'value' => $num($site->monthly_traffic_value !== null ? (float) $site->monthly_traffic_value : null), 'icon' => 'google'],
                            ['label' => 'Backlink', 'value' => $num($site->backlinks_value !== null ? (float) $site->backlinks_value : null), 'icon' => 'ahrefs'],
                            ['label' => 'Max Link Çıkışı', 'value' => $site->max_link_count !== null ? (string) $site->max_link_count : '—', 'icon' => null],
                            ['label' => 'Tahmini Teslimat', 'value' => $site->estimated_delivery ?: '—', 'icon' => null],
                        ];
                    @endphp
                    <tr
                        class="group cursor-pointer transition hover:bg-paper"
                        @click="window.location = '{{ route('sites.show', $site->domain) }}'"
                    >
                        <td class="sticky start-0 z-10 bg-white px-5 py-3.5 transition group-hover:bg-paper">
                            <a href="{{ route('sites.show', $site->domain) }}" class="flex items-center gap-x-3" @click.stop>
                                <x-site-logo :site="$site" :height="28" class="shrink-0 rounded-lg" />
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-semibold text-ink transition group-hover:text-accent-700">{{ $site->domain }}</span>
                                    <span class="block truncate text-xs text-ink-3">{{ $site->category?->name ?? 'Kategorisiz' }}</span>
                                </span>
                            </a>
                        </td>
                        <td class="px-3 py-3.5 text-center">
                            @if ($site->is_google_indexed)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-1 text-[11px] font-semibold text-emerald-700"><x-metric-icon source="google" />Var</span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-ink/5 px-2 py-1 text-[11px] font-semibold text-ink-3"><x-metric-icon source="google" />Yok</span>
                            @endif
                        </td>
                        <td class="hidden px-3 py-3.5 text-center sm:table-cell">
                            @if ($site->is_news_approved)
                                <span class="inline-flex items-center gap-1 rounded-full bg-accent-100 px-2 py-1 text-[11px] font-semibold text-accent-700"><x-metric-icon source="google" />Var</span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-ink/5 px-2 py-1 text-[11px] font-semibold text-ink-3"><x-metric-icon source="google" />Yok</span>
                            @endif
                        </td>
                        <td class="hidden px-3 py-3.5 text-center sm:table-cell">
                            <span class="text-xs font-medium text-ink-2">{{ $site->age !== null ? $site->age.' yıl' : '—' }}</span>
                        </td>
                        <td class="hidden px-3 py-3.5 text-center sm:table-cell">
                            <span class="inline-flex items-center gap-1">
                                <x-metric-icon source="moz" />
                                <span class="text-xs font-bold text-ink">{{ $num($site->da_value !== null ? (float) $site->da_value : null) }}</span>
                            </span>
                        </td>
                        <td class="hidden px-3 py-3.5 text-center sm:table-cell">
                            <span class="inline-flex items-center gap-1">
                                <x-metric-icon source="moz" />
                                <span class="text-xs font-bold text-ink">{{ $num($site->pa_value !== null ? (float) $site->pa_value : null) }}</span>
                            </span>
                        </td>
                        <td class="hidden px-3 py-3.5 text-center sm:table-cell">
                            @if ($site->is_dofollow)
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Dofollow</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-ink/5 px-2.5 py-1 text-[11px] font-semibold text-ink-3">Nofollow</span>
                            @endif
                        </td>
                        <td class="hidden px-4 py-3.5 text-end sm:table-cell">
                            @if ($hasDiscount)
                                <span class="block text-[11px] text-ink-3 line-through">{{ $money((float) $site->price, $currency) }}</span>
                                <span class="font-display text-sm font-bold text-accent-600">{{ $money((float) $site->discount_price, $currency) }}</span>
                            @else
                                <span class="font-display text-sm font-bold text-ink">{{ $money($singlePrice ?? (float) $site->price, $currency) }}</span>
                            @endif
                        </td>
                        <td class="hidden px-5 py-3.5 sm:table-cell">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    @click.stop="openRows[{{ $site->id }}] = !openRows[{{ $site->id }}]"
                                    class="inline-flex size-8 shrink-0 items-center justify-center rounded-full border border-ink/10 text-ink-2 transition hover:border-ink/25 hover:text-ink"
                                    :aria-expanded="(!!openRows[{{ $site->id }}]).toString()"
                                    aria-label="Site detaylarını göster"
                                >
                                    <svg class="size-4 shrink-0 transition" :class="openRows[{{ $site->id }}] ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                </button>

                                <form method="post" action="{{ route('cart.add') }}" @click.stop>
                                    @csrf
                                    <input type="hidden" name="product_type" value="{{ $productType }}">
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
                    </tr>
                    <tr x-show="openRows[{{ $site->id }}]" x-cloak>
                        <td colspan="9" class="bg-paper px-5 py-4">
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
