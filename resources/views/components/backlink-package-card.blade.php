@props([
    'package',
    'featureLimit' => null,
])
@php
    /** @var \App\Models\BacklinkPackage $package */
    /** @var int|null $featureLimit */
    $money = fn (float $amount): string => number_format($amount, 0, ',', '.');
    $features = collect($package->features ?? [])->values();
    $limit = $featureLimit !== null ? max(0, (int) $featureLimit) : null;
    $hasMore = $limit !== null && $features->count() > $limit;
    $visibleFeatures = $hasMore ? $features->take($limit) : $features;
    $hiddenFeatures = $hasMore ? $features->slice($limit)->values() : collect();
@endphp
<div
    @class([
        'relative flex h-full flex-col rounded-[20px] border bg-white p-6 sm:p-8',
        'border-ink shadow-pop' => $package->is_featured,
        'border-ink/10' => ! $package->is_featured,
    ])
    @if ($hasMore)
        x-data="{ showAll: false }"
    @endif
>
    @if ($package->is_featured)
        <span class="absolute -top-3 start-1/2 -translate-x-1/2 rounded-full bg-ink px-3.5 py-1 text-[11px] font-bold text-white">★ EN ÇOK TERCİH EDİLEN</span>
    @endif

    <h3 class="font-display text-xl font-semibold text-ink">{{ $package->name }}</h3>
    @if ($package->description)
        <p class="mt-2 text-sm font-medium leading-relaxed text-ink-2">{{ $package->description }}</p>
    @endif

    <div class="mt-5">
        <p class="flex items-baseline gap-x-1.5">
            <span class="font-display text-4xl font-semibold text-ink">{{ $money((float) $package->price) }}</span>
            <span class="text-sm font-medium text-ink-2">{{ $package->currency?->value ?? 'TRY' }}</span>
        </p>
        <p class="mt-1 text-xs text-ink-3">Tek seferlik ödeme (+ KDV)</p>
    </div>

    @if ($package->competition_label)
        <span class="mt-4 inline-flex w-fit items-center rounded-full bg-accent-100 px-2.5 py-1 text-[11px] font-semibold text-accent-700">{{ $package->competition_label }}</span>
    @endif

    <form method="post" action="{{ route('cart.add') }}" class="mt-5">
        @csrf
        <input type="hidden" name="product_type" value="backlink_package">
        <input type="hidden" name="backlink_package_id" value="{{ $package->id }}">
        @guest
            <button type="button" class="inline-flex w-full items-center justify-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-3 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">
                Sepete Ekle
            </button>
        @else
            <button type="submit" class="inline-flex w-full items-center justify-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-3 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]">
                Sepete Ekle
            </button>
        @endguest
    </form>

    @auth
        <a href="{{ route('free-analysis.show') }}" class="mt-2 inline-flex w-full items-center justify-center rounded-xl border border-ink/10 px-4 py-2.5 text-xs font-semibold text-ink-2 transition hover:border-ink/25 hover:text-ink">Örnek Rapor</a>
    @else
        <button type="button" class="mt-2 inline-flex w-full items-center justify-center rounded-xl border border-ink/10 px-4 py-2.5 text-xs font-semibold text-ink-2 transition hover:border-ink/25 hover:text-ink" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">Örnek Rapor (Giriş Gerekli)</button>
    @endauth

    @if ($features->isNotEmpty())
        <ul class="mt-6 space-y-2 border-t border-ink/5 pt-6">
            @foreach ($visibleFeatures as $feature)
                <li class="flex items-start gap-x-2.5 text-[13px] font-medium text-ink-2">
                    <svg class="mt-0.5 size-4 shrink-0 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    {{ $feature }}
                </li>
            @endforeach

            @if ($hasMore)
                @foreach ($hiddenFeatures as $feature)
                    <li class="flex items-start gap-x-2.5 text-[13px] font-medium text-ink-2" x-show="showAll" x-cloak>
                        <svg class="mt-0.5 size-4 shrink-0 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        {{ $feature }}
                    </li>
                @endforeach

                <li>
                    <button
                        type="button"
                        class="mt-1 text-[13px] font-semibold text-accent-700 transition hover:text-accent-800"
                        @click="showAll = !showAll"
                        x-text="showAll ? 'Daha az göster' : 'Devamı için tıklayın'"
                    ></button>
                </li>
            @endif
        </ul>
    @endif
</div>
