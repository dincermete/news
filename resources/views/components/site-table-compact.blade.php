@props([
    'sites',
    'favoritedSiteIds' => [],
    'productType' => 'site_article',
])
@php
    $money = fn (float $amount, string $currency): string => number_format($amount, 2, ',', '.').($currency === 'TRY' ? '₺' : '$');
    $num = fn (?float $value): string => $value !== null ? number_format($value, 0, ',', '.') : '—';
@endphp
<div class="overflow-x-auto" x-data="siteTable()">
    <table
        x-ref="table"
        class="site-catalog-table w-full min-w-0 text-start sm:min-w-[480px]"
        :class="sorting && 'site-catalog-table--sorting'"
    >
        <thead>
            <tr class="site-catalog-table__head">
                <th class="site-catalog-table__th sticky start-0 z-10 min-w-[160px] px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink">
                    <button type="button" class="relative block w-full rounded-md text-start hover:text-accent-700" @click="sortBy('domain')">Site Adı</button>
                </th>
                <th class="site-catalog-table__th whitespace-nowrap px-3 py-3 text-center text-[11px] font-semibold uppercase tracking-wide text-ink">
                    <button type="button" class="relative block w-full rounded-md text-center hover:text-accent-700" @click="sortBy('da')">DA</button>
                </th>
                <th class="site-catalog-table__th whitespace-nowrap px-3 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink">
                    <button type="button" class="relative block w-full rounded-md text-end hover:text-accent-700" @click="sortBy('price')">Fiyat</button>
                </th>
                <th class="site-catalog-table__th whitespace-nowrap px-3 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink">İşlem</th>
            </tr>
        </thead>
        @foreach ($sites as $site)
            @php
                $currency = $site->currency?->value ?? (string) $site->currency;
                $hasDiscount = $site->discount_price !== null && (float) $site->discount_price < (float) $site->price;
                $isFavorited = in_array($site->id, $favoritedSiteIds, true);
                $sortPrice = $hasDiscount ? (float) $site->discount_price : (float) $site->price;
            @endphp
            <tbody
                data-sort-group
                data-id="{{ $site->id }}"
                data-domain="{{ $site->domain }}"
                data-da="{{ $site->da_value !== null ? (float) $site->da_value : '' }}"
                data-price="{{ $sortPrice }}"
            >
                <tr
                    class="site-catalog-table__row group cursor-pointer transition hover:bg-paper"
                    @click="window.location = '{{ storefront_site_url($site) }}'"
                >
                    <td class="site-catalog-table__td sticky start-0 z-10 bg-white px-4 py-3 transition group-hover:bg-paper">
                        <a href="{{ storefront_site_url($site) }}" class="flex min-w-0 items-center gap-x-2.5" @click.stop>
                            <x-site-logo :site="$site" :height="24" class="shrink-0 rounded-md" />
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-ink group-hover:text-accent-700">{{ $site->domain }}</span>
                                <span class="block truncate text-[11px] text-ink-3">{{ $site->category?->name ?? 'Kategorisiz' }}</span>
                            </span>
                        </a>
                    </td>
                    <td class="site-catalog-table__td whitespace-nowrap px-3 py-3 text-center">
                        <span class="text-xs font-bold text-ink">{{ $num($site->da_value !== null ? (float) $site->da_value : null) }}</span>
                    </td>
                    <td class="site-catalog-table__td whitespace-nowrap px-3 py-3 text-end">
                        @if ($hasDiscount)
                            <span class="block text-[10px] text-ink-3 line-through">{{ $money((float) $site->price, $currency) }}</span>
                            <span class="font-display text-sm font-bold tabular-nums text-accent-600">{{ $money((float) $site->discount_price, $currency) }}</span>
                        @else
                            <span class="font-display text-sm font-bold tabular-nums text-ink">{{ $money((float) $site->price, $currency) }}</span>
                        @endif
                    </td>
                    <td class="site-catalog-table__td whitespace-nowrap px-3 py-3">
                        <div class="flex items-center justify-end gap-1.5">
                            <form method="post" action="{{ route('cart.add') }}" @click.stop>
                                @csrf
                                <input type="hidden" name="product_type" value="{{ $productType }}">
                                <input type="hidden" name="site_id" value="{{ $site->id }}">
                                @guest
                                    <button
                                        type="button"
                                        class="inline-flex size-8 items-center justify-center rounded-full bg-gradient-to-b from-black to-[#363b3c] text-white"
                                        aria-label="Sepete ekle"
                                        onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))"
                                    >
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                    </button>
                                @else
                                    <button
                                        type="submit"
                                        class="inline-flex size-8 items-center justify-center rounded-full bg-gradient-to-b from-black to-[#363b3c] text-white"
                                        aria-label="Sepete ekle"
                                    >
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                    </button>
                                @endguest
                            </form>
                            <form method="post" action="{{ route('sites.favorite', $site) }}" @click.stop>
                                @csrf
                                <button
                                    type="submit"
                                    class="inline-flex size-8 items-center justify-center rounded-full border border-ink/10"
                                    aria-label="{{ $isFavorited ? 'Favorilerden çıkar' : 'Favorilere ekle' }}"
                                >
                                    <svg class="size-4 {{ $isFavorited ? 'text-brand-500' : 'text-ink-3' }}" viewBox="0 0 24 24" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            </tbody>
        @endforeach
    </table>
</div>
