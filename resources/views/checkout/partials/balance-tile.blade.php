@php
    $payableBalance = (float) ($payable[\App\Enums\PaymentMethod::Balance->value] ?? 0);
    $insufficient = $walletBalance + 0.00001 < $payableBalance;
@endphp
<div class="rounded-[20px] border border-ink/10 bg-white p-5">
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-800">
        Ödeme tipini bakiye ödemesi olarak seçiniz. <strong>Ödeme Yap</strong> butonuna tıklamanız durumunda tutar sırasıyla bonus, çark ödülü, satış ortaklığı komisyonu ve ana bakiyenizden düşülür.
        <p class="mt-2 font-semibold">Kullanılabilir Toplam Bakiyeniz: {{ number_format($walletBalance, 2, ',', '.') }} ₺</p>
    </div>

    @if ($insufficient)
        <div class="mt-3 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-xs text-brand-800">
            Bakiyeniz bu siparişi karşılamaya yeterli değil. Lütfen bakiye yükleyin veya başka bir ödeme yöntemi seçin.
        </div>
        <a href="{{ route('account.payment-notification') }}" class="{{ $btnDark }} mt-4">
            <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </span>
            Bakiye Yükle
        </a>
    @else
        <div class="mt-4 flex items-center justify-between rounded-xl border border-ink/10 bg-paper px-4 py-3">
            <span class="text-sm font-medium text-ink-2">Ödemeniz Gereken Tutar</span>
            <span class="font-display text-base font-bold text-ink">{{ number_format($payableBalance, 2, ',', '.') }} ₺</span>
        </div>
        <button type="submit" class="{{ $btnDark }} mt-4" :disabled="!contracts">
            <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </span>
            Ödeme Yap
        </button>
    @endif
</div>
