@php
    /** @var \App\Models\CartItem $item */
    $payload = is_array($item->content_payload) ? $item->content_payload : [];
@endphp
<div class="bg-paper p-5">
    <form method="post" action="{{ route('cart.update-content', $item) }}" enctype="multipart/form-data" class="space-y-3">
        @csrf
        @method('PATCH')

        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Hedef Link</label>
                <input type="url" name="target_url" value="{{ old('target_url', $payload['target_url'] ?? '') }}" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" placeholder="https://siteniz.com/sayfa">
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Görsel Yükleme</label>
                <input type="file" name="image" accept=".png,.jpg,.jpeg" class="block w-full rounded-xl border border-ink/10 bg-white text-sm text-ink file:me-3 file:rounded-lg file:border-0 file:bg-ink file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white">
                <p class="mt-1.5 text-[11px] text-ink-3">.png, .jpg, .jpeg — en fazla 20MB</p>
                @if (! empty($payload['image_path']))
                    <p class="mt-1.5 text-[11px] text-ink-3">Yüklü: {{ basename($payload['image_path']) }}</p>
                @endif
            </div>
        </div>
        <div>
            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Not</label>
            <textarea name="note" rows="3" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" placeholder="Story için ek notlarınız">{{ old('note', $payload['note'] ?? '') }}</textarea>
        </div>

        <button type="submit" class="inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]">
            Seçimleri Kaydet
        </button>
    </form>
</div>
