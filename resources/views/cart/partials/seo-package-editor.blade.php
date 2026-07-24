@php
    /** @var \App\Models\CartItem $item */
    $payload = is_array($item->content_payload) ? $item->content_payload : [];
    $initialKeywords = is_array($payload['keywords'] ?? null) ? $payload['keywords'] : [];
@endphp
<div
    class="bg-paper p-5"
    x-data="{
        siteAddress: {{ \Illuminate\Support\Js::from($payload['site_address'] ?? '') }},
        keywords: {{ \Illuminate\Support\Js::from($initialKeywords) }},
        draft: '',
        addKeyword() {
            const word = this.draft.trim();
            if (word === '') return;
            this.keywords.push({ word, target_url: '' });
            this.draft = '';
        },
        removeKeyword(index) {
            this.keywords.splice(index, 1);
        },
        onKeydown(event) {
            if (event.key === 'Enter' || event.key === ',') {
                event.preventDefault();
                this.addKeyword();
            }
        },
    }"
>
    <form method="post" action="{{ route('cart.update-content', $item) }}" class="space-y-4">
        @csrf
        @method('PATCH')

        <div>
            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Site Adresi</label>
            <input
                type="url"
                name="site_address"
                x-model="siteAddress"
                placeholder="https://www.siteniz.com"
                class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0"
            >
        </div>

        <div>
            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Hedef Kelimeleriniz</label>
            <div class="rounded-xl border border-ink/10 bg-white p-2">
                <template x-if="keywords.length > 0">
                    <div class="mb-2 space-y-1.5">
                        <template x-for="(keyword, index) in keywords" :key="index">
                            <div class="flex items-center gap-2 rounded-lg bg-paper px-2.5 py-1.5">
                                <span class="shrink-0 rounded-md bg-ink px-2 py-1 text-[11px] font-semibold text-white" x-text="keyword.word"></span>
                                <input
                                    type="text"
                                    x-model="keyword.target_url"
                                    placeholder="Hedef sayfa (opsiyonel) — https://siteniz.com/sayfa"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-xs text-ink-2 placeholder:text-ink-3 focus:ring-0"
                                >
                                <button type="button" class="shrink-0 text-ink-3 transition hover:text-brand-600" @click="removeKeyword(index)" aria-label="Kelimeyi kaldır">
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
                <input
                    type="text"
                    x-model="draft"
                    @keydown="onKeydown($event)"
                    @blur="addKeyword()"
                    placeholder="kelime yaz, Enter veya virgül"
                    class="block w-full border-0 bg-transparent p-1.5 text-sm text-ink placeholder:text-ink-3 focus:ring-0"
                >
            </div>
            <p class="mt-1.5 text-[11px] text-ink-3">Kelimeyi yazıp Enter'a basın. İsterseniz her kelimenin gideceği hedef sayfayı da belirtebilirsiniz.</p>
        </div>

        <div>
            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Sipariş Notu (opsiyonel)</label>
            <textarea name="note" rows="3" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" placeholder="Eklemek istediğiniz not">{{ old('note', $payload['note'] ?? '') }}</textarea>
        </div>

        <input type="hidden" name="seo_keywords" :value="JSON.stringify(keywords)">

        <button type="submit" class="inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]">
            Seçimleri Kaydet
        </button>
    </form>
</div>
