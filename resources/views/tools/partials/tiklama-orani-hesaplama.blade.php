@php
    $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
    // Yayınlanmış organik arama CTR çalışmalarının ortalamasına dayanan genel tablo (pozisyon => %CTR).
    $ctrTable = [39.8, 18.7, 10.2, 7.2, 5.1, 4.4, 3.0, 2.1, 1.9, 1.6, 1.1, 0.9, 0.8, 0.7, 0.6, 0.5, 0.5, 0.4, 0.4, 0.3];
@endphp

<div
    x-data="{
        position: 3,
        volume: 1000,
        table: {{ \Illuminate\Support\Js::from($ctrTable) }},
        get ctr() {
            const idx = Math.min(Math.max(this.position, 1), 20) - 1;
            return this.table[idx];
        },
        get clicks() {
            return Math.round((this.volume || 0) * (this.ctr / 100));
        },
    }"
    class="space-y-6"
>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="{{ $label }}">Google sıralama pozisyonu</label>
            <select x-model.number="position" class="{{ $input }}">
                @for ($i = 1; $i <= 20; $i++)
                    <option value="{{ $i }}">{{ $i }}. sıra</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="{{ $label }}">Aylık arama hacmi (opsiyonel)</label>
            <input type="number" min="0" step="1" x-model.number="volume" class="{{ $input }}">
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Ortalama tıklama oranı (CTR)</p>
            <p class="mt-2 font-display text-xl font-semibold text-ink" x-text="ctr.toLocaleString('tr-TR', { minimumFractionDigits: 1 }) + '%'"></p>
        </div>
        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Beklenen aylık tıklama</p>
            <p class="mt-2 font-display text-xl font-semibold text-ink" x-text="clicks.toLocaleString('tr-TR')"></p>
        </div>
    </div>

    <p class="text-xs text-ink-3">Değerler, yayınlanmış organik arama CTR çalışmalarının genel ortalamasına dayanır; sektöre, marka bilinirliğine ve sonuç sayfasındaki reklam/özellik yoğunluğuna göre sapma gösterebilir.</p>
</div>
