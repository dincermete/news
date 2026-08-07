@props([
    'bundle',
])
@php
    /** @var \App\Models\SiteBundle $bundle */
    $money = fn (float $amount, string $currency): string => number_format($amount, 2, ',', '.').($currency === 'TRY' ? '₺' : '$');
    $sites = $bundle->sites;
    $visibleSites = $sites->take(5);
    $hasMoreSites = $sites->count() > 5;
    $fadeFrom = $bundle->resolvedBgColorFrom();
    $cartIcon = 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z';
@endphp
<div
    class="flex h-full flex-col rounded-[20px] p-5 transition hover:-translate-y-0.5 hover:shadow-pop"
    style="{{ $bundle->cardBackgroundStyle() }}"
>
    <a href="{{ $bundle->canonicalUrl() }}" class="flex flex-1 flex-col focus:outline-hidden">
        {{-- Üst satır: ikon çip + site sayısı --}}
        <div class="flex items-center justify-between gap-3">
            <span
                class="inline-flex size-10 shrink-0 items-center justify-center rounded-[10px] text-white shadow-soft"
                style="{{ $bundle->iconBadgeStyle() }}"
            >
                {{ svg($bundle->resolvedIcon(), 'size-5') }}
            </span>
            <span class="shrink-0 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-semibold text-ink-2 shadow-soft">{{ $bundle->sites_count }} Site</span>
        </div>

        <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ $bundle->name }}</h3>
        @if ($bundle->description)
            <p class="mt-1 line-clamp-2 text-sm text-ink-2">{{ $bundle->description }}</p>
        @endif

        {{-- Pakete dahil siteler (en fazla 5) --}}
        @if ($visibleSites->isNotEmpty())
            <div class="relative mt-4 flex-1">
                <ul class="space-y-1.5">
                    @foreach ($visibleSites as $site)
                        <li class="rounded-lg border border-[#e3e3e0] bg-white px-3 py-2">
                            <x-site-identity
                                :site="$site"
                                :height="28"
                                :linked="false"
                                domain-class="block truncate text-sm font-semibold text-ink"
                            />
                        </li>
                    @endforeach
                </ul>

                @if ($hasMoreSites)
                    <div
                        class="pointer-events-none absolute inset-x-0 bottom-0 h-[4.5rem]"
                        style="background: linear-gradient(to bottom, transparent 0%, {{ $fadeFrom }}33 40%, #f7f8f9 100%);"
                        aria-hidden="true"
                    ></div>
                    <p class="relative z-[1] -mt-2 text-center text-xs font-semibold text-ink">
                        Devamını gör
                        <span class="font-medium text-ink-3">(+{{ $sites->count() - 5 }})</span>
                    </p>
                @endif
            </div>
        @else
            <div class="mt-4 flex-1"></div>
        @endif
    </a>

    {{-- Fiyat + sepete ekle --}}
    <div class="mt-4 flex items-center justify-between gap-3 border-t border-ink/10 pt-4">
        <span class="font-display text-lg font-bold text-ink">{{ $money((float) $bundle->price, $bundle->currency?->value ?? 'TRY') }}</span>
        <form method="post" action="{{ route('cart.add') }}">
            @csrf
            <input type="hidden" name="product_type" value="bundle">
            <input type="hidden" name="site_bundle_id" value="{{ $bundle->id }}">
            @guest
                <button
                    type="button"
                    class="group inline-flex items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-4 py-2 text-xs font-semibold text-white transition hover:scale-[1.04] active:scale-[0.98]"
                    onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))"
                >
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $cartIcon }}"/></svg>
                    Sepete Ekle
                </button>
            @else
                <button
                    type="submit"
                    class="group inline-flex items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-4 py-2 text-xs font-semibold text-white transition hover:scale-[1.04] active:scale-[0.98]"
                >
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $cartIcon }}"/></svg>
                    Sepete Ekle
                </button>
            @endguest
        </form>
    </div>
</div>
