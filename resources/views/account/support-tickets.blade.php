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

    <div class="space-y-5" x-data="{ open: false }">
        <div>
            <p><span class="{{ $chip }}">Hesabım</span></p>
            <h1 class="mt-3 font-display text-2xl font-medium text-ink sm:text-[28px]">Destek Taleplerim</h1>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach ([
                '' => ['Hepsi', $counts['all']],
                'open' => ['Açık', $counts['open']],
                'answered' => ['Yanıtlandı', $counts['answered']],
                'closed' => ['Kapatıldı', $counts['closed']],
            ] as $key => [$label, $count])
                <a
                    href="{{ route('account.support-tickets', array_filter(['status' => $key ?: null, 'q' => $search ?: null])) }}"
                    @class([
                        'rounded-[10px] px-3.5 py-2 text-xs font-semibold transition',
                        'bg-ink text-white' => $statusFilter === $key,
                        'bg-paper text-ink-2 hover:text-ink' => $statusFilter !== $key,
                    ])
                >
                    {{ $label }} ({{ $count }})
                </a>
            @endforeach
        </div>

        <form method="get" action="{{ route('account.support-tickets') }}" class="flex gap-2">
            @if ($statusFilter !== '')
                <input type="hidden" name="status" value="{{ $statusFilter }}">
            @endif
            <input type="search" name="q" value="{{ $search }}" placeholder="Talep ara…" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink placeholder:text-ink-3 focus:border-ink/30 focus:ring-0">
            <button type="submit" class="shrink-0 rounded-xl border border-ink/10 bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:border-ink/25">Ara</button>
        </form>

        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <button type="button" class="group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 text-sm font-medium text-white transition hover:scale-[1.02] active:scale-[0.98]" @click="open = !open">
                <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </span>
                Yeni Talep Oluştur
            </button>

            <form method="post" action="{{ route('account.support-tickets.store') }}" class="mt-4 space-y-3" x-show="open" x-cloak>
                @csrf
                <div>
                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Konu</label>
                    <input type="text" name="subject" required class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" value="{{ old('subject') }}">
                </div>
                <div>
                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Mesaj</label>
                    <textarea name="body" rows="4" required class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0">{{ old('body') }}</textarea>
                </div>
                <button type="submit" class="rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]">
                    Gönder
                </button>
            </form>
        </div>

        <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
            @if ($tickets->isEmpty())
                <p class="px-5 py-16 text-center text-sm text-ink-2">Destek talebi bulunamadı.</p>
            @else
                <ul class="divide-y divide-ink/5">
                    @foreach ($tickets as $ticket)
                        <li class="px-5 py-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-ink">{{ $ticket->subject }}</p>
                                    <p class="mt-1 line-clamp-2 text-xs text-ink-2">{{ $ticket->body }}</p>
                                </div>
                                <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $tone($ticket->status?->getColor()) }}">
                                    {{ $ticket->status === \App\Enums\SupportTicketStatus::InProgress ? 'Yanıtlandı' : $ticket->status?->getLabel() }}
                                </span>
                            </div>
                            <p class="mt-2 text-[11px] font-medium text-ink-3">{{ $ticket->created_at?->format('d.m.Y H:i') }}</p>
                        </li>
                    @endforeach
                </ul>
                <div class="border-t border-ink/10 px-5 py-4">
                    {{ $tickets->links('vendor.pagination.storefront') }}
                </div>
            @endif
        </div>
    </div>
@endsection
