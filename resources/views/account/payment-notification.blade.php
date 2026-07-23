@extends('layouts.account')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('content')
    @php
        $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
        $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
        $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
        $tone = fn (?string $color) => match ($color) {
            'success' => 'bg-emerald-100 text-emerald-700',
            'warning' => 'bg-amber-100 text-amber-700',
            'danger' => 'bg-brand-100 text-brand-700',
            'info', 'primary' => 'bg-accent-100 text-accent-700',
            default => 'bg-ink/5 text-ink-3',
        };
    @endphp

    <div class="space-y-5">
        <div>
            <p><span class="{{ $chip }}">Hesabım</span></p>
            <h1 class="mt-3 font-display text-2xl font-medium text-ink sm:text-[28px]">Ödeme Bildirimi</h1>
            <p class="mt-1.5 text-sm text-ink-2">Havale / EFT bildirimi gönderin</p>
        </div>

        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <h2 class="font-display text-base font-semibold text-ink">Banka hesapları</h2>
            <ul class="mt-4 space-y-2.5">
                @forelse ($banks as $bank)
                    <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-ink/10 bg-white px-4 py-3 text-sm" x-data="{ copied: false }">
                        <div>
                            <p class="font-semibold text-ink">{{ $bank['name'] }}</p>
                            <p class="text-xs text-ink-2">{{ $bank['account_name'] }}</p>
                            <p class="mt-0.5 font-mono text-xs text-ink-2">{{ $bank['iban'] }}</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-xl border border-ink/10 bg-white px-3 py-1.5 text-xs font-semibold text-ink transition hover:border-ink/25"
                            @click="navigator.clipboard.writeText(@js($bank['iban'])); copied = true; setTimeout(() => copied = false, 1500)"
                        >
                            <span x-text="copied ? 'Kopyalandı' : 'Kopyala'"></span>
                        </button>
                    </li>
                @empty
                    <li class="text-sm text-ink-2">Banka hesabı tanımlı değil.</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <h2 class="font-display text-base font-semibold text-ink">Bildirim formu</h2>

            @if ($pendingPayments->isEmpty())
                <p class="mt-3 text-sm text-ink-2">Bekleyen havale ödemeniz yok. Sipariş oluşturduktan sonra burada görünecektir.</p>
            @else
                <form method="post" action="{{ route('payment.bank-transfer-notify') }}" class="mt-4 space-y-3" x-data="{ paymentId: '{{ $pendingPayments->first()->id }}', amounts: {{ \Illuminate\Support\Js::from($pendingPayments->mapWithKeys(fn ($p) => [(string) $p->id => (float) $p->amount])) }} }">
                    @csrf
                    <div>
                        <label class="{{ $label }}">Ödeme</label>
                        <select name="payment_id" x-model="paymentId" class="{{ $input }}">
                            @foreach ($pendingPayments as $payment)
                                <option value="{{ $payment->id }}">
                                    #{{ $payment->id }} — {{ number_format((float) $payment->amount, 2, ',', '.') }} {{ $payment->currency?->value ?? 'TRY' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $label }}">Banka</label>
                        <select name="bank_name" required class="{{ $input }}">
                            <option value="">Seçin</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank['name'] }}">{{ $bank['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $label }}">Ad Soyad</label>
                        <input type="text" name="payer_name" required value="{{ old('payer_name', auth()->user()->name) }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Tutar</label>
                        <input type="text" class="{{ $input }} bg-paper" readonly :value="(amounts[paymentId] ?? 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺'">
                    </div>
                    <div>
                        <label class="{{ $label }}">Açıklama</label>
                        <textarea name="payer_note" rows="2" class="{{ $input }}">{{ old('payer_note') }}</textarea>
                    </div>
                    <button type="submit" class="group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 text-sm font-medium text-white transition hover:scale-[1.02] active:scale-[0.98]">
                        <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                            <svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </span>
                        Bildirim gönder
                    </button>
                </form>
            @endif
        </div>

        <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
            <div class="border-b border-ink/10 px-5 py-4">
                <h2 class="font-display text-base font-semibold text-ink">Geçmiş bildirimler</h2>
            </div>
            @if ($history->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-ink-2">Henüz bildirim yok.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[620px] border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-ink/10 bg-paper">
                                <th class="px-5 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Banka</th>
                                <th class="px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Gönderen</th>
                                <th class="px-4 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tutar</th>
                                <th class="px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Durum</th>
                                <th class="px-5 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tarih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/5">
                            @foreach ($history as $payment)
                                <tr class="transition hover:bg-paper">
                                    <td class="px-5 py-3.5 text-ink">{{ $payment->bank_name }}</td>
                                    <td class="px-4 py-3.5 text-ink-2">{{ $payment->payer_name }}</td>
                                    <td class="px-4 py-3.5 text-end font-semibold text-ink">{{ number_format((float) $payment->amount, 2, ',', '.') }} ₺</td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $tone($payment->status?->getColor()) }}">
                                            {{ $payment->status?->getLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-end text-ink-3">{{ $payment->updated_at?->format('d.m.Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
