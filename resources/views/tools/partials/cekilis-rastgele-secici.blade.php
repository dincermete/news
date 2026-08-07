@php
    $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
    $btnDark = 'inline-flex items-center justify-center gap-x-2 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60';
    $btnGhost = 'inline-flex items-center justify-center rounded-xl border border-ink/10 bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:border-ink/25';
@endphp

<div
    x-data="{
        raw: '',
        remaining: [],
        winners: [],
        drawing: false,
        get participants() {
            return this.raw.split('\n').map((s) => s.trim()).filter(Boolean);
        },
        start() {
            this.remaining = [...this.participants];
            this.winners = [];
        },
        pickRandomIndex(max) {
            const array = new Uint32Array(1);
            crypto.getRandomValues(array);
            return array[0] % max;
        },
        draw() {
            if (this.remaining.length === 0) {
                this.remaining = [...this.participants];
            }
            if (this.remaining.length === 0 || this.drawing) {
                return;
            }
            this.drawing = true;
            let ticks = 0;
            const interval = setInterval(() => {
                ticks++;
                const idx = this.pickRandomIndex(this.remaining.length);
                if (ticks < 12) {
                    this.flash = this.remaining[idx];
                    return;
                }
                clearInterval(interval);
                const [winner] = this.remaining.splice(idx, 1);
                this.winners.unshift(winner);
                this.flash = null;
                this.drawing = false;
            }, 80);
        },
        flash: null,
    }"
    class="space-y-6"
    x-init="$watch('raw', () => start())"
>
    <div>
        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Katılımcılar (her satıra bir isim)</label>
        <textarea x-model="raw" rows="6" placeholder="Ayşe&#10;Mehmet&#10;Zeynep" class="{{ $input }}"></textarea>
        <p class="mt-1.5 text-xs text-ink-3" x-text="participants.length + ' katılımcı'"></p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button type="button" class="{{ $btnDark }}" :disabled="participants.length === 0 || drawing" @click="draw()">
            <span x-show="!drawing">Kazananı seç</span>
            <span x-show="drawing" x-cloak>Seçiliyor…</span>
        </button>
        <button type="button" class="{{ $btnGhost }}" @click="start()" x-show="winners.length > 0">Listeyi sıfırla</button>
    </div>

    <div class="rounded-[20px] border border-ink/10 bg-paper p-8 text-center">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Kazanan</p>
        <p class="mt-2 font-display text-2xl font-semibold text-ink" x-text="flash || (winners[0] ?? '—')"></p>
    </div>

    <template x-if="winners.length > 0">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tüm kazananlar</p>
            <ol class="mt-2 space-y-1.5">
                <template x-for="(winner, index) in winners" :key="index">
                    <li class="flex items-center gap-x-2 rounded-lg bg-white px-3 py-2 text-sm text-ink">
                        <span class="text-ink-3" x-text="(winners.length - index) + '.'"></span>
                        <span x-text="winner"></span>
                    </li>
                </template>
            </ol>
        </div>
    </template>

    <p class="text-xs text-ink-3">Seçim, tarayıcınızın kriptografik rastgele sayı üretecini (crypto.getRandomValues) kullanır; hiçbir veri sunucuya gönderilmez.</p>
</div>
