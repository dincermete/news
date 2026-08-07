@php
    $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
    $btnDark = 'inline-flex items-center justify-center gap-x-2 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]';
    $btnGhost = 'inline-flex items-center justify-center rounded-xl border border-ink/10 bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:border-ink/25';
@endphp

<div
    x-data="{
        siteName: '',
        siteUrl: '',
        description: '',
        sections: [
            { title: '', url: '', note: '' },
        ],
        addSection() {
            this.sections.push({ title: '', url: '', note: '' });
        },
        removeSection(index) {
            this.sections.splice(index, 1);
        },
        get output() {
            let out = `# ${this.siteName || 'Site Adı'}\n\n`;
            if (this.description) {
                out += `> ${this.description}\n\n`;
            }
            const valid = this.sections.filter((s) => s.title && s.url);
            if (valid.length > 0) {
                out += `## Bölümler\n\n`;
                valid.forEach((s) => {
                    out += `- [${s.title}](${s.url})` + (s.note ? `: ${s.note}` : '') + `\n`;
                });
            }
            return out.trim() + '\n';
        },
        copied: false,
        copy() {
            navigator.clipboard.writeText(this.output);
            this.copied = true;
            setTimeout(() => this.copied = false, 1500);
        },
        download() {
            const blob = new Blob([this.output], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'llms.txt';
            a.click();
            URL.revokeObjectURL(url);
        },
    }"
    class="space-y-6"
>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="{{ $label }}">Site adı</label>
            <input type="text" x-model="siteName" placeholder="Tanıtım Yazısı" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Site URL</label>
            <input type="text" x-model="siteUrl" placeholder="https://siteniz.com" class="{{ $input }}">
        </div>
        <div class="sm:col-span-2">
            <label class="{{ $label }}">Kısa açıklama</label>
            <textarea x-model="description" rows="2" placeholder="Sitenizin ne yaptığını bir cümlede özetleyin." class="{{ $input }}"></textarea>
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between gap-3">
            <label class="{{ $label }} mb-0">Bölümler</label>
            <button type="button" class="text-xs font-semibold text-accent-700 hover:text-accent-800" @click="addSection()">+ Bölüm ekle</button>
        </div>
        <div class="mt-2 space-y-3">
            <template x-for="(section, index) in sections" :key="index">
                <div class="grid gap-2 rounded-xl border border-ink/10 bg-paper p-3 sm:grid-cols-[1fr_1fr_1fr_auto]">
                    <input type="text" x-model="section.title" placeholder="Başlık (örn. Fiyatlandırma)" class="{{ $input }} bg-white">
                    <input type="text" x-model="section.url" placeholder="URL" class="{{ $input }} bg-white">
                    <input type="text" x-model="section.note" placeholder="Kısa not (opsiyonel)" class="{{ $input }} bg-white">
                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-ink/10 bg-white px-3 text-ink-3 transition hover:border-brand-300 hover:text-brand-600" @click="removeSection(index)" aria-label="Bölümü kaldır">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
        <div class="flex items-center justify-between gap-3">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Önizleme</p>
            <div class="flex gap-2">
                <button type="button" class="{{ $btnGhost }}" @click="copy()">
                    <span x-show="!copied">Kopyala</span>
                    <span x-show="copied" x-cloak>Kopyalandı</span>
                </button>
                <button type="button" class="{{ $btnDark }}" @click="download()">llms.txt indir</button>
            </div>
        </div>
        <pre class="mt-3 max-h-64 overflow-auto rounded-xl bg-white p-4 text-xs leading-relaxed text-ink" x-text="output"></pre>
    </div>

    <p class="text-xs text-ink-3">İndirdiğiniz dosyayı sitenizin kök dizinine <code class="rounded bg-paper px-1 py-0.5">/llms.txt</code> olarak erişilebilecek şekilde yükleyin.</p>
</div>
