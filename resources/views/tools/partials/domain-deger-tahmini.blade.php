@php
    $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
@endphp

<div
    x-data="{
        age: 5,
        da: 25,
        traffic: 1000,
        tld: 'com',
        multipliers: { com: 1.2, com_tr: 1.1, net: 0.9, diger: 0.8 },
        get value() {
            const base = Math.pow(this.da || 0, 1.5) * 8 + (this.age || 0) * 150 + (this.traffic || 0) * 0.15;
            return Math.round(base * this.multipliers[this.tld]);
        },
        fmt(n) {
            return Number(n || 0).toLocaleString('tr-TR', { maximumFractionDigits: 0 });
        },
    }"
    class="space-y-6"
>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="{{ $label }}">Domain yaşı (yıl)</label>
            <input type="number" min="0" max="100" step="1" x-model.number="age" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Domain Otoritesi (DA, 0-100)</label>
            <input type="number" min="0" max="100" step="1" x-model.number="da" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Aylık tahmini trafik</label>
            <input type="number" min="0" step="1" x-model.number="traffic" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Uzantı</label>
            <select x-model="tld" class="{{ $input }}">
                <option value="com">.com</option>
                <option value="com_tr">.com.tr</option>
                <option value="net">.net</option>
                <option value="diger">Diğer</option>
            </select>
        </div>
    </div>

    <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tahmini domain değeri</p>
        <p class="mt-2 font-display text-2xl font-semibold text-ink" x-text="fmt(value) + ' ₺'"></p>
    </div>

    <p class="text-xs text-ink-3">
        Formül: (DA<sup>1.5</sup> × 8) + (yaş × 150) + (trafik × 0,15), uzantı katsayısıyla çarpılır. Bu, alım satım kararlarında tek başına kullanılmaması gereken kaba bir eğitim amaçlı tahmindir.
    </p>
</div>
