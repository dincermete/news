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
            <p class="mt-1.5 text-sm text-ink-2">Havale/EFT ödeme bildirimleriniz ve banka hesap bilgilerimiz.</p>
        </div>

        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <h2 class="font-display text-base font-semibold text-ink">Ödeme Bildirimi Yap</h2>
            <p class="mt-1 text-sm text-ink-2">Havale/EFT ile gönderdiğiniz ödemeyi bildirin</p>

            @if ($pendingPayments->isEmpty())
                <p class="mt-3 text-sm text-ink-2">Bekleyen havale ödemeniz yok. Sipariş oluşturduktan sonra burada görünecektir.</p>
            @else
                <form
                    method="post"
                    action="{{ route('payment.bank-transfer-notify') }}"
                    class="mt-4 space-y-3"
                    x-data="{
                        paymentId: '{{ $pendingPayments->first()->id }}',
                        amounts: {{ \Illuminate\Support\Js::from($pendingPayments->mapWithKeys(fn ($p) => [(string) $p->id => (float) $p->amount])) }},
                        refs: {{ \Illuminate\Support\Js::from($pendingPayments->mapWithKeys(fn ($p) => [(string) $p->id => $p->reference_code])) }},
                        copiedRef: false,
                    }"
                >
                    @csrf
                    @if ($pendingPayments->count() > 1)
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
                    @else
                        <input type="hidden" name="payment_id" :value="paymentId">
                    @endif

                    <div x-show="refs[paymentId]" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-accent-200 bg-accent-50/60 px-4 py-3 text-sm text-accent-800">
                        <span>Açıklamaya yazmanız gereken referans: <strong class="font-mono" x-text="refs[paymentId]"></strong></span>
                        <button
                            type="button"
                            class="shrink-0 rounded-lg border border-accent-200 bg-white px-3 py-1.5 text-xs font-semibold text-accent-800 transition hover:border-accent-300"
                            @click="navigator.clipboard.writeText(refs[paymentId]); copiedRef = true; setTimeout(() => copiedRef = false, 1500)"
                        >
                            <span x-show="!copiedRef">Kopyala</span>
                            <span x-show="copiedRef" x-cloak>Kopyalandı</span>
                        </button>
                    </div>

                    <div>
                        <label class="{{ $label }}">Banka</label>
                        <select name="bank_name" required class="{{ $input }}">
                            <option value="">Banka seçin...</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $label }}">Gönderen Ad Soyad</label>
                        <input type="text" name="payer_name" required value="{{ old('payer_name', auth()->user()->name) }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Tutar (₺)</label>
                        <input
                            type="text"
                            class="{{ $input }} bg-paper"
                            readonly
                            value="{{ number_format((float) $pendingPayments->first()->amount, 2, ',', '.') }} ₺"
                            :value="(amounts[paymentId] ?? 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺'"
                        >
                    </div>
                    <div>
                        <label class="{{ $label }}">Açıklama</label>
                        <textarea name="payer_note" rows="2" placeholder="Sipariş no / kullanıcı kodunuz / referans (opsiyonel)" class="{{ $input }}">{{ old('payer_note') }}</textarea>
                    </div>
                    <button type="submit" class="group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 text-sm font-medium text-white transition hover:scale-[1.02] active:scale-[0.98]">
                        <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                            <svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </span>
                        Bildirimi Gönder
                    </button>
                    <p class="text-xs text-ink-2">Bildiriminiz ekibimizce kontrol edilip eşleştirilecektir. Açıklamaya sipariş no/kullanıcı kodunuzu yazmanız onayı hızlandırır.</p>
                </form>
            @endif
        </div>

        <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
            <div class="border-b border-ink/10 px-5 py-4">
                <h2 class="font-display text-base font-semibold text-ink">Geçmiş Bildirimlerim</h2>
                <p class="mt-0.5 text-xs text-ink-3">Son 5 bildirim</p>
            </div>
            @if ($history->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-ink-2">Henüz ödeme bildiriminiz yok.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[680px] border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-ink/10 bg-paper">
                                <th class="px-5 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tarih</th>
                                <th class="px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Banka</th>
                                <th class="px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Referans</th>
                                <th class="px-4 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tutar</th>
                                <th class="px-5 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Durum</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/5">
                            @foreach ($history as $payment)
                                <tr class="transition hover:bg-paper">
                                    <td class="px-5 py-3.5 text-ink-3">{{ $payment->updated_at?->format('d.m.Y H:i') }}</td>
                                    <td class="px-4 py-3.5 text-ink">{{ $payment->bank_name }}</td>
                                    <td class="px-4 py-3.5 font-mono text-xs text-ink-2">{{ $payment->reference_code ?? '—' }}</td>
                                    <td class="px-4 py-3.5 text-end font-semibold text-ink">{{ number_format((float) $payment->amount, 2, ',', '.') }} ₺</td>
                                    <td class="px-5 py-3.5 text-end">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $tone($payment->status?->getColor()) }}">
                                            {{ $payment->status?->getLabel() }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <h2 class="font-display text-base font-semibold text-ink">Banka Hesaplarımız</h2>
            <p class="mt-1 text-xs text-ink-2">Açıklamaya kullanıcı kodunuzu yazmayı unutmayın</p>
            <ul class="mt-4 space-y-2.5">
                @forelse ($banks as $bank)
                    <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-ink/10 bg-white px-4 py-3 text-sm" x-data="{ copied: false }">
                        <div class="flex items-center gap-3">
                            @if ($bank->short_code)
                                <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-ink/5 text-[11px] font-bold text-ink-2">{{ $bank->short_code }}</span>
                            @endif
                            <div>
                                <p class="font-semibold text-ink">{{ $bank->name }}</p>
                                <p class="text-xs text-ink-2">{{ $bank->account_name }}</p>
                                <p class="mt-0.5 font-mono text-xs text-ink-2">{{ $bank->iban }}</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="rounded-xl border border-ink/10 bg-white px-3 py-1.5 text-xs font-semibold text-ink transition hover:border-ink/25"
                            @click="navigator.clipboard.writeText(@js($bank->iban)); copied = true; setTimeout(() => copied = false, 1500)"
                        >
                            <span x-text="copied ? 'Kopyalandı' : 'Kopyala'"></span>
                        </button>
                    </li>
                @empty
                    <li class="text-sm text-ink-2">Banka hesabı tanımlı değil.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
