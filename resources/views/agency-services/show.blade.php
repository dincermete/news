@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var array<string, mixed> $service */

    $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    $h2 = 'font-display text-3xl font-medium leading-[1.2] tracking-[-0.01em] text-ink sm:text-[44px] lg:text-[52px]';
    $sub = 'text-lg font-medium leading-relaxed text-ink-2';
    $btnWhite = 'group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-white to-[#c9c9c9] p-1 pe-4 text-sm font-medium text-ink transition hover:scale-[1.03] active:scale-[0.98]';
    $btnGhostDark = 'group inline-flex items-center gap-x-3 rounded-2xl border border-ink/10 bg-white p-1 pe-4 text-sm font-medium text-ink transition hover:bg-paper-2 hover:scale-[1.03] active:scale-[0.98]';
    $btnDark = 'group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-4 text-sm font-medium text-white transition hover:scale-[1.03] active:scale-[0.98]';
    $btnChip = 'inline-flex size-8 items-center justify-center rounded-xl';
    $arrowIcon = '<svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>';
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-light relative overflow-hidden rounded-3xl text-ink">
            <div class="relative mx-auto flex max-w-3xl flex-col items-center px-5 pb-14 pt-16 text-center sm:px-8 lg:pb-20 lg:pt-24" data-reveal-group>
                <p class="inline-flex items-center gap-x-2 rounded-full border border-ink/10 bg-white py-1 pe-3.5 ps-1 text-xs text-ink-2 shadow-soft" data-reveal>
                    <a href="{{ route('agency-services.index') }}" class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Hizmetler</a>
                    {{ $service['name'] }}
                </p>

                <h1 class="mt-5 font-display text-4xl font-medium leading-[1.1] sm:text-5xl" data-reveal>
                    {{ $service['name'] }}
                </h1>

                <p class="mt-5 max-w-xl text-lg font-medium leading-relaxed text-ink-2" data-reveal>
                    {{ $service['subtitle'] }}
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3" data-reveal>
                    <a href="{{ route('contact.show') }}" class="{{ $btnWhite }}">
                        <span class="{{ $btnChip }} bg-white/15 text-white">{!! $arrowIcon !!}</span>
                        Teklif Alın
                    </a>
                    <a href="tel:{{ $siteSettings->support_phone ?: '08503052241' }}" class="{{ $btnGhostDark }}">
                        <span class="{{ $btnChip }} bg-ink/5 text-ink">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        </span>
                        {{ $siteSettings->support_phone_display ?: '0850 305 22 41' }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= KAPSAM ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <p data-reveal><span class="{{ $chip }}">Kapsam</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>Neler sunuyoruz</h2>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2">
            @foreach ($service['coverage'] as $item)
                <div class="rounded-[20px] border border-ink/10 bg-white p-6 transition hover:-translate-y-0.5 hover:shadow-pop sm:p-8" data-reveal>
                    <h3 class="font-display text-lg font-semibold text-ink">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm font-medium leading-relaxed text-ink-2">{{ $item['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= NEDEN BİZ ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
            <div>
                <p data-reveal><span class="{{ $chip }}">Neden biz</span></p>
                <h2 class="mt-5 {{ $h2 }}" data-reveal>{{ $service['name'] }} sürecinde farkımız</h2>
            </div>

            <ul class="space-y-4" data-reveal>
                @foreach ($service['benefits'] as $benefit)
                    <li class="flex items-start gap-x-3 rounded-2xl bg-paper p-4">
                        <svg class="mt-0.5 size-5 shrink-0 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        <span class="text-sm font-medium leading-relaxed text-ink">{{ $benefit }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ================= DİĞER HİZMETLER ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <p data-reveal><span class="{{ $chip }}">Diğer hizmetler</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>Kampanyanızı büyüten diğer hizmetlerimiz</h2>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (\App\Support\AgencyServicePages::all() as $other)
                @continue($other['slug'] === $service['slug'])
                <a href="{{ route('agency-services.show', $other['slug']) }}" class="group flex flex-col rounded-[20px] bg-paper p-6 transition hover:-translate-y-0.5 hover:shadow-pop" data-reveal>
                    <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-[10px] bg-ink text-white">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $other['icon'] }}"/></svg>
                    </span>
                    <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ $other['name'] }}</h3>
                    <p class="mt-1.5 text-sm font-medium leading-relaxed text-ink-2">{{ $other['excerpt'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ================= SSS ================= --}}
    <section class="mx-auto max-w-6xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div class="lg:sticky lg:top-28 lg:self-start" data-reveal-group>
                <p data-reveal><span class="{{ $chip }}">SSS</span></p>
                <h2 class="mt-5 max-w-xs {{ $h2 }}" data-reveal>Merak edilenler</h2>
                <p class="mt-4 {{ $sub }}" data-reveal>Cevabınızı bulamadınız mı?</p>
                <a href="tel:{{ $siteSettings->support_phone ?: '08503052241' }}" class="{{ $btnDark }} mt-4" data-reveal>
                    <span class="{{ $btnChip }} bg-white/15 text-white">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                    </span>
                    Bizimle konuşun
                </a>
            </div>

            <div class="space-y-3" data-reveal-group>
                @foreach ($service['faqs'] as $index => $faq)
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
        </div>
    </section>

    {{-- ================= KAPANIŞ CTA ================= --}}
    <section class="px-2 pb-2 sm:px-3">
        <div class="panel-light relative overflow-hidden rounded-3xl text-ink">
            <div class="relative mx-auto flex max-w-2xl flex-col items-center px-5 py-16 text-center sm:px-8" data-reveal-group>
                <h2 class="font-display text-3xl font-medium leading-[1.2] sm:text-[40px]" data-reveal>
                    {{ $service['name'] }} için teklif alın
                </h2>
                <p class="mt-4 max-w-md text-lg font-medium leading-relaxed text-ink-2" data-reveal>
                    Hedeflerinizi ve bütçenizi bize iletin; size özel bir plan hazırlayalım.
                </p>
                <div class="mt-7 flex flex-wrap items-center justify-center gap-3" data-reveal>
                    <a href="{{ route('contact.show') }}" class="{{ $btnWhite }}">
                        <span class="{{ $btnChip }} bg-white/15 text-white">{!! $arrowIcon !!}</span>
                        Teklif Alın
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
