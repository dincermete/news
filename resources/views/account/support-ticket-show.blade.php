@extends('layouts.account')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('content')
    @php
        $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
        $tone = fn (?string $color) => match ($color) {
            'success' => 'bg-emerald-100 text-emerald-700',
            'warning' => 'bg-yellow-100 text-yellow-700',
            'danger' => 'bg-brand-100 text-brand-700',
            'info', 'primary' => 'bg-accent-100 text-accent-700',
            default => 'bg-ink/5 text-ink-3',
        };
    @endphp

    <div class="space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p><span class="{{ $chip }}">Destek</span></p>
                <h1 class="mt-3 font-display text-2xl font-medium text-ink sm:text-[28px]">{{ $ticket->subject }}</h1>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-ink-3">
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $tone($ticket->status?->getColor()) }}">
                        {{ $ticket->status === \App\Enums\SupportTicketStatus::InProgress ? 'Yanıtlandı' : $ticket->status?->getLabel() }}
                    </span>
                    <span>#{{ $ticket->id }}</span>
                    <span>{{ $ticket->created_at?->format('d.m.Y H:i') }}</span>
                </div>
            </div>
            <a href="{{ route('account.support-tickets') }}" class="text-sm font-medium text-ink-3 hover:text-ink">Taleplerime dön</a>
        </div>

        <div class="space-y-3 rounded-[20px] border border-ink/10 bg-white p-5">
            @forelse ($ticket->messages as $message)
                <div @class([
                    'rounded-2xl px-4 py-3',
                    'bg-paper' => ! $message->is_staff,
                    'bg-accent-50 border border-accent-100' => $message->is_staff,
                ])>
                    <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] font-semibold uppercase tracking-wide text-ink-3">
                        <span>{{ $message->is_staff ? 'Destek ekibi' : 'Siz' }}</span>
                        <span>{{ $message->created_at?->format('d.m.Y H:i') }}</span>
                    </div>
                    <p class="mt-2 whitespace-pre-wrap text-sm text-ink">{{ $message->body }}</p>
                </div>
            @empty
                <p class="text-sm text-ink-2">Henüz mesaj yok.</p>
            @endforelse
        </div>

        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <h2 class="font-display text-base font-semibold text-ink">Yanıt yaz</h2>
            @if ($ticket->status === \App\Enums\SupportTicketStatus::Closed)
                <p class="mt-1 text-xs text-ink-3">Bu talep kapalı. Yanıt gönderirseniz yeniden açılır.</p>
            @endif
            <form method="post" action="{{ route('account.support-tickets.reply', $ticket) }}" class="mt-4 space-y-3">
                @csrf
                <textarea
                    name="body"
                    rows="4"
                    required
                    maxlength="5000"
                    class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0"
                    placeholder="Mesajınız…"
                >{{ old('body') }}</textarea>
                @error('body')
                    <p class="text-xs text-brand-700">{{ $message }}</p>
                @enderror
                <button type="submit" class="rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]">
                    Gönder
                </button>
            </form>
        </div>
    </div>
@endsection
