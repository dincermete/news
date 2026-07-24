@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\SiteBundle> $bundles */
    $money = fn (float $amount, string $currency): string => number_format($amount, 2, ',', '.').($currency === 'TRY' ? '₺' : '$');
@endphp

@section('content')
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto flex max-w-6xl flex-col items-center px-5 pb-10 pt-14 text-center sm:px-8 sm:pb-12 sm:pt-16">
                <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80">
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Tanıtım Paketleri</span>
                    {{ $bundles->count() }} hazır paket
                </p>
                <h1 class="mt-5 max-w-2xl font-display text-4xl font-medium leading-[1.12] sm:text-5xl">
                    Birden fazla siteyi tek pakette satın alın
                </h1>
                <p class="mt-4 max-w-xl text-lg font-medium leading-relaxed text-white/65">
                    Hazır tanıtım paketleri ile tek işlemde çok sayıda sitede yayın alın.
                </p>

                <form method="get" action="{{ route('bundles.index') }}" class="mt-7 flex w-full max-w-xl items-center gap-2 rounded-full border border-white/15 bg-white p-1.5 shadow-pop" role="search">
                    <svg class="ms-3 size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <input type="search" name="q" value="{{ $q }}" placeholder="Paket adı ara" class="w-full border-0 bg-transparent p-0 py-2 text-sm text-ink placeholder:text-ink-3 focus:ring-0" aria-label="Paket ara">
                    <button type="submit" class="inline-flex shrink-0 items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.03] active:scale-[0.98]">
                        Ara
                    </button>
                </form>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        @if ($bundles->isEmpty())
            <div class="rounded-[20px] border border-ink/10 bg-paper px-6 py-16 text-center">
                <p class="font-display text-lg font-semibold text-ink">Bu aramaya uygun paket bulunamadı.</p>
                <a href="{{ route('bundles.index') }}" class="mt-5 inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white">Tüm Paketleri Gör</a>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($bundles as $bundle)
                    <div class="flex h-full flex-col rounded-[20px] border border-ink/10 bg-white transition hover:-translate-y-0.5 hover:shadow-pop" x-data="{ open: false }">
                        <div class="flex flex-auto flex-col p-5">
                            <span class="inline-flex w-fit items-center rounded-full bg-accent-100 px-2.5 py-1 text-[11px] font-semibold text-accent-700">{{ $bundle->sites_count }} Site</span>
                            <h2 class="mt-3 font-display text-lg font-semibold text-ink">{{ $bundle->name }}</h2>
                            @if ($bundle->description)
                                <p class="mt-1.5 line-clamp-3 text-sm text-ink-2">{{ $bundle->description }}</p>
                            @endif

                            @if ($bundle->sites->isNotEmpty())
                                <button
                                    type="button"
                                    class="mt-3 inline-flex w-fit items-center gap-x-1.5 text-xs font-semibold text-ink-2 transition hover:text-ink"
                                    @click="open = !open"
                                    :aria-expanded="open.toString()"
                                >
                                    <svg class="size-3.5 shrink-0 transition" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                    <span x-text="open ? 'Siteleri gizle' : 'Sitelere bak'"></span>
                                </button>

                                <div x-show="open" x-transition x-cloak class="mt-3 space-y-1.5 border-t border-ink/5 pt-3">
                                    @foreach ($bundle->sites as $site)
                                        <div class="flex items-center gap-x-2">
                                            <x-site-favicon :domain="$site->domain" :size="18" class="shrink-0 rounded-md" />
                                            <span class="truncate text-xs text-ink-2">{{ $site->domain }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="mt-auto flex items-center justify-between gap-3 border-t border-ink/5 px-5 py-4">
                            <span class="font-display text-lg font-bold text-ink">{{ $money((float) $bundle->price, $bundle->currency?->value ?? 'TRY') }}</span>
                            <form method="post" action="{{ route('cart.add') }}">
                                @csrf
                                <input type="hidden" name="product_type" value="bundle">
                                <input type="hidden" name="site_bundle_id" value="{{ $bundle->id }}">
                                @guest
                                    <button type="button" class="inline-flex items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-3.5 py-1.5 text-xs font-semibold text-white transition hover:scale-[1.04] active:scale-[0.98]" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">
                                        Sepete Ekle
                                    </button>
                                @else
                                    <button type="submit" class="inline-flex items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-3.5 py-1.5 text-xs font-semibold text-white transition hover:scale-[1.04] active:scale-[0.98]">
                                        Sepete Ekle
                                    </button>
                                @endguest
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
