@php
    /** @var \App\Models\CartItem $item */
    $payload = is_array($item->content_payload) ? $item->content_payload : [];
@endphp
<div class="bg-paper p-5">
    <form method="post" action="{{ route('cart.update-content', $item) }}" class="space-y-3">
        @csrf
        @method('PATCH')

        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Site Adresi</label>
                <input type="url" name="target_url" value="{{ old('target_url', $payload['target_url'] ?? '') }}" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" placeholder="https://ornek.com">
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Anahtar Kelime</label>
                <input type="text" name="keywords" value="{{ old('keywords', $payload['keywords'] ?? '') }}" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" placeholder="Örn. SEO danışmanlık">
            </div>
        </div>
        <div>
            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Not</label>
            <textarea name="note" rows="3" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" placeholder="Footer link için ek notlarınız">{{ old('note', $payload['note'] ?? '') }}</textarea>
        </div>

        <button type="submit" class="inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]">
            Seçimleri Kaydet
        </button>
    </form>
</div>
