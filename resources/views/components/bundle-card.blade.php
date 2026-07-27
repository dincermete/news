@props([
    'bundle',
])
@php
    /** @var \App\Models\SiteBundle $bundle */
    $money = fn (float $amount, string $currency): string => number_format($amount, 2, ',', '.').($currency === 'TRY' ? '₺' : '$');
@endphp
<div class="flex h-full flex-col rounded-[20px] bg-paper p-5 transition hover:-translate-y-0.5 hover:shadow-pop">
    <a href="{{ route('bundles.show', $bundle->slug) }}" class="flex flex-1 flex-col focus:outline-hidden">
        {{-- Üst satır: ikon çip + site sayısı --}}
        <div class="flex items-center justify-between gap-3">
            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-[10px] bg-gradient-to-br from-brand-500 to-brand-600 text-white">
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
            </span>
            <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-ink-2 shadow-soft">{{ $bundle->sites_count }} Site</span>
        </div>

        <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ $bundle->name }}</h3>
        @if ($bundle->description)
            <p class="mt-1 line-clamp-2 text-sm text-ink-2">{{ $bundle->description }}</p>
        @endif

        {{-- Pakete dahil siteler --}}
        @if ($bundle->sites->isNotEmpty())
            <ul class="scrollbar-brand mt-4 max-h-[190px] flex-1 space-y-1.5 overflow-y-auto pe-1">
                @foreach ($bundle->sites as $site)
                    <li class="flex items-center gap-3 rounded-xl bg-white px-3 py-2 shadow-soft">
                        <x-site-logo :site="$site" :height="20" class="shrink-0 rounded" />
                        <span class="min-w-0 flex-1 truncate text-xs font-medium text-ink-2">{{ $site->domain }}</span>
                    </li>
                @endforeach
            </ul>
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
                    Sepete Ekle
                    <svg class="size-3.5 transition group-hover:rotate-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </button>
            @else
                <button
                    type="submit"
                    class="group inline-flex items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-4 py-2 text-xs font-semibold text-white transition hover:scale-[1.04] active:scale-[0.98]"
                >
                    Sepete Ekle
                    <svg class="size-3.5 transition group-hover:rotate-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </button>
            @endguest
        </form>
    </div>
</div>
