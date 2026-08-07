@php
    $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
@endphp

<div
    x-data="{
        text: '',
        get words() {
            const trimmed = this.text.trim();
            return trimmed === '' ? 0 : trimmed.split(/\s+/).length;
        },
        get charsWithSpaces() {
            return this.text.length;
        },
        get charsWithoutSpaces() {
            return this.text.replace(/\s/g, '').length;
        },
        get metaState() {
            const len = this.charsWithSpaces;
            if (len === 0) return { label: 'Meta açıklama uzunluğu bekleniyor', tone: 'bg-ink/5 text-ink-3' };
            if (len < 120) return { label: 'Kısa — 150-160 karakter hedefleyin', tone: 'bg-yellow-100 text-yellow-700' };
            if (len <= 160) return { label: 'İdeal aralıkta', tone: 'bg-emerald-100 text-emerald-700' };
            return { label: 'Uzun — Google kesebilir', tone: 'bg-brand-100 text-brand-700' };
        },
    }"
    class="space-y-6"
>
    <div>
        <textarea x-model="text" rows="8" placeholder="Metninizi buraya yapıştırın veya yazın…" class="{{ $input }}"></textarea>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Kelime</p>
            <p class="mt-2 font-display text-xl font-semibold text-ink" x-text="words.toLocaleString('tr-TR')"></p>
        </div>
        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Karakter (boşluklu)</p>
            <p class="mt-2 font-display text-xl font-semibold text-ink" x-text="charsWithSpaces.toLocaleString('tr-TR')"></p>
        </div>
        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Karakter (boşluksuz)</p>
            <p class="mt-2 font-display text-xl font-semibold text-ink" x-text="charsWithoutSpaces.toLocaleString('tr-TR')"></p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="metaState.tone" x-text="metaState.label"></span>
        <span class="text-xs text-ink-3">Meta açıklama için Google genellikle 150-160 karakter civarını kesmeden gösterir.</span>
    </div>
</div>
