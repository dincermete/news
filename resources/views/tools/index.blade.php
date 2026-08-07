@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var array<string, string> $categories */
    /** @var array<string, array<int, array<string, mixed>>> $grouped */

    $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    $h2 = 'font-display text-3xl font-medium leading-[1.2] tracking-[-0.01em] text-ink sm:text-[44px] lg:text-[52px]';
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-light relative overflow-hidden rounded-3xl text-ink">
            <div class="relative mx-auto flex max-w-3xl flex-col items-center px-5 pb-14 pt-16 text-center sm:px-8 lg:pb-20 lg:pt-24" data-reveal-group>
                <p class="inline-flex items-center gap-x-2 rounded-full border border-ink/10 bg-white py-1 pe-3.5 ps-1 text-xs text-ink-2 shadow-soft" data-reveal>
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Ücretsiz</span>
                    Üyeliksiz, anında sonuç
                </p>

                <h1 class="mt-5 font-display text-4xl font-medium leading-[1.1] sm:text-5xl" data-reveal>
                    SEO ve içerik araçları
                </h1>

                <p class="mt-5 max-w-xl text-lg font-medium leading-relaxed text-ink-2" data-reveal>
                    Kampanya kurmadan önce hızlıca hesaplayın, üretin ve kontrol edin — hepsi ücretsiz, kayıt gerektirmeden.
                </p>
            </div>
        </div>
    </section>

    @foreach ($categories as $key => $label)
        @continue(empty($grouped[$key]))
        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8" data-reveal-group>
            <div class="mx-auto max-w-2xl text-center">
                <p data-reveal><span class="{{ $chip }}">{{ $label }}</span></p>
            </div>

            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($grouped[$key] as $tool)
                    <a href="{{ route('tools.show', $tool['slug']) }}" class="group flex flex-col rounded-[20px] bg-paper p-6 transition hover:-translate-y-0.5 hover:shadow-pop" data-reveal>
                        <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-[10px] bg-ink text-white">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tool['icon'] }}"/></svg>
                        </span>
                        <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ $tool['name'] }}</h3>
                        <p class="mt-1.5 text-sm font-medium leading-relaxed text-ink-2">{{ $tool['excerpt'] }}</p>
                        <span class="mt-auto flex items-center gap-x-1.5 pt-4 text-xs font-semibold text-ink-2 transition group-hover:text-ink">
                            Aracı kullan
                            <svg class="size-3 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
@endsection
