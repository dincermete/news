@php
    $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
@endphp

<div
    x-data="{
        traffic: 2000,
        conversion: 2,
        orderValue: 1500,
        spend: 10000,
        get revenue() {
            return this.traffic * (this.conversion / 100) * this.orderValue;
        },
        get profit() {
            return this.revenue - this.spend;
        },
        get roi() {
            return this.spend > 0 ? (this.profit / this.spend) * 100 : 0;
        },
        fmt(n) {
            return Number(n || 0).toLocaleString('tr-TR', { maximumFractionDigits: 0 });
        },
    }"
    class="space-y-6"
>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="{{ $label }}">Aylık organik trafik (ziyaret)</label>
            <input type="number" min="0" step="1" x-model.number="traffic" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Dönüşüm oranı (%)</label>
            <input type="number" min="0" max="100" step="0.1" x-model.number="conversion" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Ortalama sipariş/işlem değeri (₺)</label>
            <input type="number" min="0" step="1" x-model.number="orderValue" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Aylık SEO yatırımı (₺)</label>
            <input type="number" min="0" step="1" x-model.number="spend" class="{{ $input }}">
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tahmini aylık gelir</p>
            <p class="mt-2 font-display text-xl font-semibold text-ink" x-text="fmt(revenue) + ' ₺'"></p>
        </div>
        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Kâr / zarar</p>
            <p class="mt-2 font-display text-xl font-semibold" :class="profit >= 0 ? 'text-emerald-600' : 'text-brand-600'" x-text="(profit >= 0 ? '+' : '') + fmt(profit) + ' ₺'"></p>
        </div>
        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">ROI</p>
            <p class="mt-2 font-display text-xl font-semibold" :class="roi >= 0 ? 'text-emerald-600' : 'text-brand-600'" x-text="(roi >= 0 ? '+' : '') + roi.toLocaleString('tr-TR', { maximumFractionDigits: 1 }) + '%'"></p>
        </div>
    </div>

    <p class="text-xs text-ink-3">Bu hesaplama girdiğiniz varsayımlara dayanan bir tahmindir; gerçek sonuçlar trafik kalitesine ve rekabete göre değişir.</p>
</div>
