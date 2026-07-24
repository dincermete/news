@if ($paytrToken)
    <div class="rounded-[20px] border border-ink/10 bg-white p-4">
        <div class="mb-4 flex items-start gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-xs text-emerald-800">
            <svg class="mt-0.5 size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            <span>Aşağıdaki güvenli pencerede kart bilgilerinizi girin. Tüm işlem <strong>PayTR</strong> altyapısı üzerinden 3D Secure ile doğrulanır; kart bilgileri sistemimizde saklanmaz.</span>
        </div>
        <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
        <iframe
            src="https://www.paytr.com/odeme/guvenli/{{ $paytrToken }}"
            id="paytriframe"
            frameborder="0"
            scrolling="no"
            style="width: 100%; min-height: 600px;"
        ></iframe>
        <script>iFrameResize({}, '#paytriframe');</script>
    </div>
@else
    <div class="rounded-[20px] border border-ink/10 bg-white p-5">
        <p class="text-sm text-ink-2">Kart bilgilerinizi bir sonraki adımda güvenli PayTR ekranında gireceksiniz.</p>
        <div class="mt-4 flex items-center justify-between rounded-xl border border-ink/10 bg-paper px-4 py-3">
            <span class="text-sm font-medium text-ink-2">Ödemeniz Gereken Tutar</span>
            <span
                class="font-display text-base font-bold text-ink"
                x-text="(payable['card'] ?? 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺'"
            ></span>
        </div>
        <button type="submit" class="{{ $btnDark }} mt-4" :disabled="!contracts">
            <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </span>
            Ödeme Yap
        </button>
        <div class="mt-4 flex flex-wrap items-center gap-2">
            @foreach (['Troy', 'Amex', 'Visa', 'Mastercard', 'PayTR'] as $brand)
                <span class="inline-flex items-center rounded-lg border border-ink/10 bg-paper px-2.5 py-1 text-[11px] font-semibold text-ink-2">{{ $brand }}</span>
            @endforeach
        </div>
    </div>
@endif
