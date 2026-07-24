@extends('layouts.account')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('content')
    @php
        $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
        $tone = fn (?string $color) => match ($color) {
            'success' => 'bg-emerald-100 text-emerald-700',
            'warning' => 'bg-amber-100 text-amber-700',
            'danger' => 'bg-brand-100 text-brand-700',
            'info', 'primary' => 'bg-accent-100 text-accent-700',
            default => 'bg-ink/5 text-ink-3',
        };
    @endphp

    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p><span class="{{ $chip }}">Hesabım</span></p>
                <h1 class="mt-3 font-display text-2xl font-medium text-ink sm:text-[28px]">SEO Analizlerim</h1>
            </div>
            <a href="{{ route('free-analysis.show') }}" class="group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 text-sm font-medium text-white transition hover:scale-[1.02] active:scale-[0.98]">
                <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </span>
                Yeni Analiz Talep Et
            </a>
        </div>

        <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
            @if ($analyses->isEmpty())
                <p class="px-5 py-16 text-center text-sm text-ink-2">Henüz analiz talebiniz yok.</p>
            @else
                <ul class="divide-y divide-ink/5">
                    @foreach ($analyses as $analysis)
                        <li class="px-5 py-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $analysis->site_url }}</p>
                                    <p class="mt-1 text-xs text-ink-2">{{ $analysis->service_type?->getLabel() }}</p>
                                    @if ($analysis->brief)
                                        <p class="mt-1.5 line-clamp-2 text-xs text-ink-3">{{ $analysis->brief }}</p>
                                    @endif
                                </div>
                                <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $tone($analysis->status?->getColor()) }}">
                                    {{ $analysis->status?->getLabel() }}
                                </span>
                            </div>

                            @if ($analysis->status === \App\Enums\SeoAnalysisStatus::Completed && $analysis->result)
                                <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Analiz Sonucu</p>
                                    <p class="mt-1 whitespace-pre-line text-sm text-emerald-900">{{ $analysis->result }}</p>
                                </div>
                            @endif

                            <p class="mt-2 text-[11px] font-medium text-ink-3">{{ $analysis->created_at?->format('d.m.Y H:i') }}</p>
                        </li>
                    @endforeach
                </ul>
                <div class="border-t border-ink/10 px-5 py-4">
                    {{ $analyses->links('vendor.pagination.storefront') }}
                </div>
            @endif
        </div>
    </div>
@endsection
