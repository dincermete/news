@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var array<string, mixed> $tool */
    /** @var array<string, string> $categories */

    $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    $h2 = 'font-display text-3xl font-medium leading-[1.2] tracking-[-0.01em] text-ink sm:text-[44px] lg:text-[52px]';
    $btnWhite = 'group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-white to-[#c9c9c9] p-1 pe-4 text-sm font-medium text-ink transition hover:scale-[1.03] active:scale-[0.98]';
    $btnChip = 'inline-flex size-8 items-center justify-center rounded-xl';
    $arrowIcon = '<svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>';

    $others = collect(\App\Support\Tools::all())
        ->where('category', $tool['category'])
        ->where('slug', '!=', $tool['slug'])
        ->take(3);
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-light relative overflow-hidden rounded-3xl text-ink">
            <div class="relative mx-auto flex max-w-3xl flex-col items-center px-5 pb-14 pt-16 text-center sm:px-8 lg:pb-20 lg:pt-24" data-reveal-group>
                <p class="inline-flex items-center gap-x-2 rounded-full border border-ink/10 bg-white py-1 pe-3.5 ps-1 text-xs text-ink-2 shadow-soft" data-reveal>
                    <a href="{{ route('tools.index') }}" class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">{{ $categories[$tool['category']] }}</a>
                    {{ $tool['name'] }}
                </p>

                <h1 class="mt-5 font-display text-4xl font-medium leading-[1.1] sm:text-5xl" data-reveal>
                    {{ $tool['name'] }}
                </h1>

                <p class="mt-5 max-w-xl text-lg font-medium leading-relaxed text-ink-2" data-reveal>
                    {{ $tool['excerpt'] }}
                </p>
            </div>
        </div>
    </section>

    {{-- ================= ARAÇ ================= --}}
    <section class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8" data-reveal-group>
        <div class="rounded-[20px] border border-ink/10 bg-white p-5 sm:p-8" data-reveal>
            @include($tool['partial'], ['tool' => $tool])
        </div>
    </section>

    {{-- ================= SSS ================= --}}
    @if (! empty($tool['faqs']))
        <section class="mx-auto max-w-4xl px-4 pb-14 sm:px-6 lg:px-8" data-reveal-group>
            <p data-reveal><span class="{{ $chip }}">SSS</span></p>
            <div class="mt-5 space-y-3">
                @foreach ($tool['faqs'] as $index => $faq)
                    <div x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }" class="rounded-2xl bg-paper" data-reveal>
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-4 px-6 py-5 text-start focus:outline-hidden"
                            @click="open = !open"
                            :aria-expanded="open.toString()"
                        >
                            <span class="text-sm font-medium text-ink">{{ $faq['q'] }}</span>
                            <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full border border-ink/10 bg-white text-ink transition-transform duration-300" :class="open ? 'rotate-45' : ''">
                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            </span>
                        </button>
                        <div x-show="open" x-cloak class="px-6 pb-5 text-[13px] font-medium leading-relaxed text-ink-2">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ================= DİĞER ARAÇLAR ================= --}}
    @if ($others->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8" data-reveal-group>
            <div class="mx-auto max-w-2xl text-center">
                <p data-reveal><span class="{{ $chip }}">Bununla ilgili araçlar</span></p>
            </div>

            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($others as $other)
                    <a href="{{ route('tools.show', $other['slug']) }}" class="group flex flex-col rounded-[20px] bg-paper p-6 transition hover:-translate-y-0.5 hover:shadow-pop" data-reveal>
                        <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-[10px] bg-ink text-white">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $other['icon'] }}"/></svg>
                        </span>
                        <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ $other['name'] }}</h3>
                        <p class="mt-1.5 text-sm font-medium leading-relaxed text-ink-2">{{ $other['excerpt'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ================= KAPANIŞ CTA ================= --}}
    @if (! empty($tool['related_route']) && \Illuminate\Support\Facades\Route::has($tool['related_route']))
        <section class="px-2 pb-2 sm:px-3">
            <div class="panel-light relative overflow-hidden rounded-3xl text-ink">
                <div class="relative mx-auto flex max-w-2xl flex-col items-center px-5 py-16 text-center sm:px-8" data-reveal-group>
                    <h2 class="font-display text-3xl font-medium leading-[1.2] sm:text-[40px]" data-reveal>
                        Aracı beğendiniz mi? İşi bize bırakın
                    </h2>
                    <p class="mt-4 max-w-md text-lg font-medium leading-relaxed text-ink-2" data-reveal>
                        Bu hesaplama size fikir verdi; gerçek sonuç için doğru siteyi ve paketi biz bulalım.
                    </p>
                    <div class="mt-7 flex flex-wrap items-center justify-center gap-3" data-reveal>
                        <a href="{{ route($tool['related_route']) }}" class="{{ $btnWhite }}">
                            <span class="{{ $btnChip }} bg-white/15 text-white">{!! $arrowIcon !!}</span>
                            {{ $tool['related_cta'] }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection
