@extends('layouts.account')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('content')
    @php
        $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    @endphp

    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p><span class="{{ $chip }}">Hesabım</span></p>
                <h1 class="mt-3 font-display text-2xl font-medium text-ink sm:text-[28px]">Favori Ürünlerim</h1>
            </div>
            <a href="{{ route('sites.index') }}" class="inline-flex items-center gap-x-1.5 text-sm font-semibold text-accent-700 hover:text-accent-800">
                Siteleri İncele
                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>

        @if ($favorites->isEmpty())
            <div class="rounded-[20px] border border-ink/10 bg-paper px-6 py-16 text-center">
                <p class="text-sm font-medium text-ink-2">Henüz favori ürününüz yok</p>
                <a href="{{ route('sites.index') }}" class="group mt-5 inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 text-sm font-medium text-white transition hover:scale-[1.02] active:scale-[0.98]">
                    <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                        <svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </span>
                    Siteleri İncele
                </a>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($favorites as $favorite)
                    @if ($favorite->site)
                        <div class="flex items-start justify-between gap-3 rounded-[20px] border border-ink/10 bg-paper p-5">
                            <div class="flex min-w-0 items-start gap-x-3">
                                <x-site-favicon :domain="$favorite->site->domain" :size="32" class="mt-0.5 shrink-0 rounded-lg" />
                                <div class="min-w-0">
                                    <a href="{{ route('sites.show', $favorite->site->domain) }}" class="block truncate text-sm font-semibold text-ink transition hover:text-accent-700">
                                        {{ $favorite->site->domain }}
                                    </a>
                                    <p class="mt-0.5 truncate text-xs text-ink-3">{{ $favorite->site->category?->name }}</p>
                                </div>
                            </div>
                            <form method="post" action="{{ route('account.favorites.destroy', $favorite) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex size-8 shrink-0 items-center justify-center rounded-xl border border-ink/10 bg-white text-ink-2 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600" aria-label="Favoriden kaldır">
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </form>
                        </div>
                    @endif
                @endforeach
            </div>
            <div>{{ $favorites->links('vendor.pagination.storefront') }}</div>
        @endif
    </div>
@endsection
