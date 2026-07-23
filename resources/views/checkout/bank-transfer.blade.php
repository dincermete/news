@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
@endphp

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:px-8">
        <div>
            <h1 class="font-display text-2xl font-medium text-ink sm:text-[28px]">Havale / EFT</h1>
            <p class="mt-1.5 text-sm text-ink-2">
                Sipariş #{{ $orderGroup->id }} — ödenecek tutar:
                <span class="font-semibold text-ink">{{ number_format((float) $payment->amount, 2, ',', '.') }} {{ $payment->currency?->value ?? 'TRY' }}</span>
            </p>
        </div>

        <div class="mt-6 rounded-[20px] border border-ink/10 bg-paper p-5">
            <h2 class="font-display text-base font-semibold text-ink">Banka hesapları</h2>
            <ul class="mt-4 space-y-3">
                @forelse ($banks as $bank)
                    <li class="rounded-xl border border-ink/10 bg-white px-4 py-3 text-sm">
                        <p class="font-semibold text-ink">{{ $bank['name'] }}</p>
                        <p class="mt-0.5 text-ink-2">{{ $bank['account_name'] }}</p>
                        <p class="mt-0.5 font-mono text-xs text-ink-2">{{ $bank['iban'] }}</p>
                    </li>
                @empty
                    <li class="text-sm text-ink-2">Banka hesabı tanımlı değil.</li>
                @endforelse
            </ul>
        </div>

        <div class="mt-5 rounded-[20px] border border-ink/10 bg-paper p-5">
            <h2 class="font-display text-base font-semibold text-ink">Havale bildirimi</h2>
            <form method="post" action="{{ route('payment.bank-transfer-notify') }}" class="mt-4 space-y-3">
                @csrf
                <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                <div>
                    <label class="{{ $label }}">Banka</label>
                    <input type="text" name="bank_name" required class="{{ $input }}" list="bank-names">
                    <datalist id="bank-names">
                        @foreach ($banks as $bank)
                            <option value="{{ $bank['name'] }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="{{ $label }}">Gönderen ad soyad</label>
                    <input type="text" name="payer_name" required class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Not</label>
                    <textarea name="payer_note" rows="2" class="{{ $input }}"></textarea>
                </div>
                <button type="submit" class="group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 text-sm font-medium text-white transition hover:scale-[1.02] active:scale-[0.98]">
                    <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                        <svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </span>
                    Bildirim gönder
                </button>
            </form>
        </div>
    </div>
@endsection
