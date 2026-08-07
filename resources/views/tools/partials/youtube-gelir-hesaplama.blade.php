@php
    $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
@endphp

<div
    x-data="{
        views: 100000,
        rpm: 15,
        monetizedShare: 50,
        get estimate() {
            return (this.views || 0) * ((this.monetizedShare || 0) / 100) * ((this.rpm || 0) / 1000);
        },
        get low() {
            return this.estimate * 0.7;
        },
        get high() {
            return this.estimate * 1.3;
        },
        fmt(n) {
            return Number(n || 0).toLocaleString('tr-TR', { maximumFractionDigits: 0 });
        },
    }"
    class="space-y-6"
>
    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label class="{{ $label }}">İzlenme sayısı</label>
            <input type="number" min="0" step="1" x-model.number="views" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Tahmini RPM (₺ / 1000 izlenme)</label>
            <input type="number" min="0" step="0.5" x-model.number="rpm" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Reklamlı izlenme oranı (%)</label>
            <input type="number" min="0" max="100" step="1" x-model.number="monetizedShare" class="{{ $input }}">
        </div>
    </div>

    <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tahmini gelir aralığı</p>
        <p class="mt-2 font-display text-2xl font-semibold text-ink">
            <span x-text="fmt(low) + ' ₺'"></span> — <span x-text="fmt(high) + ' ₺'"></span>
        </p>
        <p class="mt-1 text-xs text-ink-2">Orta nokta tahmini: <span x-text="fmt(estimate) + ' ₺'"></span></p>
    </div>

    <p class="text-xs text-ink-3">Türkiye'de kanal niş ve mevsime göre RPM genellikle 5-40 ₺ arasında değişir. Bu hesaplama girdiğiniz varsayımlara dayanır; gerçek YouTube ödemesi doğrudan YouTube tarafından hesaplanır.</p>
</div>
