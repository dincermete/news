@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var list<\App\Enums\SeoAnalysisServiceType> $serviceTypes */
@endphp

@section('content')
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto grid max-w-4xl gap-10 px-5 pb-16 pt-16 sm:px-8 lg:grid-cols-2 lg:items-center lg:pb-20 lg:pt-20">
                <div>
                    <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80">
                        <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Ücretsiz Görüşme</span>
                        24 Saatte Dönüş
                    </p>

                    <h1 class="mt-5 font-display text-4xl font-medium leading-[1.1] sm:text-5xl">
                        Projenize özel ücretsiz analiz
                    </h1>

                    <p class="mt-5 max-w-md text-lg font-medium leading-relaxed text-white/65">
                        Sitenizi analiz edelim; hizmete ve hedeflerinize göre size en uygun yol haritasını 24 saat içinde panelinizde paylaşalım.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-white/70">
                        <span>3.600+ markanın güvendiği ajans</span>
                        <span>10+ yıllık SEO & GEO deneyimi</span>
                    </div>
                </div>

                <div class="rounded-[20px] border border-white/10 bg-white/[0.04] p-6 sm:p-8">
                    @guest
                        <div class="flex flex-col items-center gap-y-4 py-8 text-center">
                            <span class="inline-flex size-12 items-center justify-center rounded-full bg-white/10 text-white">
                                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                            </span>
                            <p class="font-display text-lg font-semibold text-white">Analiz talebi göndermek için giriş yapın</p>
                            <p class="text-sm text-white/60">Analiz sonucunuzu hesabım panelinizden takip edebilmeniz için önce giriş yapmanız gerekiyor.</p>
                            <button
                                type="button"
                                class="group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-white to-[#c9c9c9] p-1 pe-5 text-sm font-medium text-ink transition hover:scale-[1.03] active:scale-[0.98]"
                                onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))"
                            >
                                <span class="inline-flex size-8 items-center justify-center rounded-xl bg-gradient-to-b from-black to-[#363b3c] text-white">
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                </span>
                                Giriş yap
                            </button>
                        </div>
                    @else
                        <form method="post" action="{{ route('free-analysis.store') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-white/50">Site adresiniz *</label>
                                <input type="url" name="site_url" required value="{{ old('site_url') }}" placeholder="https://siteniz.com" class="block w-full rounded-xl border border-white/15 bg-white/5 px-3 py-2.5 text-sm text-white placeholder:text-white/40 focus:border-white/30 focus:ring-0">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-white/50">Hangi hizmet için görüşüyoruz? *</label>
                                <select name="service_type" required class="block w-full rounded-xl border border-white/15 bg-white/5 px-3 py-2.5 text-sm text-white focus:border-white/30 focus:ring-0">
                                    @foreach ($serviceTypes as $type)
                                        <option value="{{ $type->value }}" class="text-ink" @selected(old('service_type') === $type->value)>{{ $type->getLabel() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-white/50">Kısaca anlatmak istediğiniz</label>
                                <textarea name="brief" rows="3" class="block w-full rounded-xl border border-white/15 bg-white/5 px-3 py-2.5 text-sm text-white placeholder:text-white/40 focus:border-white/30 focus:ring-0" placeholder="Daha detaylı brief vermek ister misiniz? Daha kapsamlı analiz için faydalı.">{{ old('brief') }}</textarea>
                            </div>
                            <button type="submit" class="group inline-flex w-full items-center justify-center gap-x-3 rounded-2xl bg-gradient-to-b from-white to-[#c9c9c9] p-1 pe-5 text-sm font-medium text-ink transition hover:scale-[1.02] active:scale-[0.98]">
                                <span class="inline-flex size-8 items-center justify-center rounded-xl bg-gradient-to-b from-black to-[#363b3c] text-white">
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                </span>
                                Analiz Talebi Gönder
                            </button>
                            <p class="text-center text-[11px] text-white/40">Talep sonrası 24 saat içinde dönüş yapılır; sonucu hesabım panelinizden takip edebilirsiniz.</p>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </section>
@endsection
