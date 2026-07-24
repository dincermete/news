@php
    $label = $label ?? 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $input = $input ?? 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
@endphp
<div class="rounded-[20px] border border-ink/10 bg-white p-5">
    <p class="text-xs text-ink-2">
        Sipariş onayından sonra panele banka hesap bilgilerimiz iletilecektir. Açıklamaya sipariş numaranızı yazarak
        <strong>havale/EFT yapın</strong>; ödeme onayından sonra ekibimiz yayını başlatır.
    </p>

    <ul class="mt-4 space-y-2.5">
        @forelse ($banks as $bank)
            <li
                class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-ink/10 bg-paper px-4 py-3"
                x-data="{ copied: false }"
            >
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-ink">{{ $bank['name'] }}</p>
                    <p class="text-xs text-ink-2">{{ $bank['account_name'] }}</p>
                    <p class="mt-0.5 font-mono text-xs text-ink-2">{{ $bank['iban'] }}</p>
                </div>
                <button
                    type="button"
                    class="shrink-0 rounded-lg border border-ink/10 bg-white px-3 py-1.5 text-xs font-semibold text-ink-2 transition hover:border-ink/25"
                    @click="navigator.clipboard.writeText('{{ $bank['iban'] }}'); copied = true; setTimeout(() => copied = false, 1500)"
                >
                    <span x-show="!copied">Kopyala</span>
                    <span x-show="copied" x-cloak>Kopyalandı</span>
                </button>
            </li>
        @empty
            <li class="text-sm text-ink-2">Banka hesabı tanımlı değil.</li>
        @endforelse
    </ul>

    @if ($bankTransferPayment)
        <div class="mt-5 border-t border-ink/10 pt-5">
            <h3 class="font-display text-sm font-semibold text-ink">Ödeme Bildirimi</h3>
            <form method="post" action="{{ route('payment.bank-transfer-notify') }}" class="mt-3 space-y-3">
                @csrf
                <input type="hidden" name="payment_id" value="{{ $bankTransferPayment->id }}">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="{{ $label }}">Banka Adı</label>
                        <input type="text" name="bank_name" required class="{{ $input }}" list="bank-names-list">
                        <datalist id="bank-names-list">
                            @foreach ($banks as $bank)
                                <option value="{{ $bank['name'] }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="{{ $label }}">Ödenecek Tutar</label>
                        <input type="text" value="{{ number_format((float) $bankTransferPayment->amount, 2, ',', '.') }} {{ $bankTransferPayment->currency?->value ?? 'TRY' }}" class="{{ $input }} bg-paper" readonly>
                    </div>
                </div>
                <div>
                    <label class="{{ $label }}">Ad Soyad</label>
                    <input type="text" name="payer_name" required class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Açıklama</label>
                    <textarea name="payer_note" rows="2" class="{{ $input }}"></textarea>
                </div>
                <button type="submit" class="{{ $btnDark }}">
                    <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </span>
                    Siparişi Tamamla
                </button>
            </form>
        </div>
    @else
        <div class="mt-5 flex items-center justify-between rounded-xl border border-ink/10 bg-paper px-4 py-3">
            <span class="text-sm font-medium text-ink-2">Ödemeniz Gereken Tutar</span>
            <span
                class="font-display text-base font-bold text-ink"
                x-text="(payable['bank_transfer'] ?? 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺'"
            ></span>
        </div>
        <button type="submit" class="{{ $btnDark }} mt-4" :disabled="!contracts">
            <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </span>
            Ödeme Yap
        </button>
    @endif
</div>
