@php
    $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
    $btnDark = 'shrink-0 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]';

    $prefixes = ['en iyi', 'ücretsiz', 'nasıl', 'en ucuz', '2026'];
    $suffixes = [
        'nedir', 'nasıl yapılır', 'fiyatları', 'yorumları', 'en iyileri',
        'rehberi', 'avantajları', 'dezavantajları', 'alternatifleri', 'karşılaştırması',
        'kampanyaları', 'örnekleri', 'ipuçları', 'fiyat listesi', 'nereden alınır',
    ];
@endphp

<div
    x-data="{
        seed: '',
        prefixes: {{ \Illuminate\Support\Js::from($prefixes) }},
        suffixes: {{ \Illuminate\Support\Js::from($suffixes) }},
        get ideas() {
            const seed = this.seed.trim();
            if (! seed) {
                return [];
            }
            const fromSuffix = this.suffixes.map((s) => `${seed} ${s}`);
            const fromPrefix = this.prefixes.map((p) => `${p} ${seed}`);
            return [...fromPrefix, ...fromSuffix];
        },
        copied: false,
        copyAll() {
            navigator.clipboard.writeText(this.ideas.join('\n'));
            this.copied = true;
            setTimeout(() => this.copied = false, 1500);
        },
    }"
    class="space-y-6"
>
    <div class="flex flex-col gap-2 sm:flex-row">
        <div class="flex-1">
            <label class="{{ $label }}">Tohum kelime</label>
            <input type="text" x-model="seed" placeholder="örn. tanıtım yazısı" class="{{ $input }}">
        </div>
    </div>

    <template x-if="ideas.length > 0">
        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <div class="flex items-center justify-between gap-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3" x-text="ideas.length + ' fikir üretildi'"></p>
                <button type="button" class="{{ $btnDark }}" @click="copyAll()">
                    <span x-show="!copied">Tümünü kopyala</span>
                    <span x-show="copied" x-cloak>Kopyalandı</span>
                </button>
            </div>
            <ul class="mt-4 grid gap-1.5 sm:grid-cols-2">
                <template x-for="idea in ideas" :key="idea">
                    <li class="rounded-lg bg-white px-3 py-2 text-sm text-ink" x-text="idea"></li>
                </template>
            </ul>
        </div>
    </template>

    <p class="text-xs text-ink-3">Bu araç arama hacmi veya zorluk verisi göstermez; kalıp kombinasyonlarıyla içerik/başlık fikri üreten bir beyin fırtınası aracıdır.</p>
</div>
