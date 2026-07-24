@php
    /** @var \App\Models\CartItem $item */
    /** @var \Illuminate\Support\Collection $wordPackages */
    $payload = is_array($item->content_payload) ? $item->content_payload : [];
    $mode = $item->content_mode?->value ?? \App\Enums\ContentMode::FileUpload->value;
@endphp
<div
    class="bg-paper p-5"
    x-data="{ tab: '{{ $mode === 'ai_article' ? 'ai' : 'file' }}' }"
>
    <div class="inline-flex items-center gap-1 rounded-2xl border border-ink/10 bg-white p-1">
        <button
            type="button"
            class="rounded-xl px-3.5 py-2 text-xs font-semibold transition"
            :class="tab === 'file' ? 'bg-ink text-white' : 'text-ink-2 hover:text-ink'"
            @click="tab = 'file'"
        >
            Dosya Yükle
        </button>
        <button
            type="button"
            class="rounded-xl px-3.5 py-2 text-xs font-semibold transition"
            :class="tab === 'ai' ? 'bg-ink text-white' : 'text-ink-2 hover:text-ink'"
            @click="tab = 'ai'"
        >
            Makale Yazdır
        </button>
    </div>

    <form method="post" action="{{ route('cart.update-content', $item) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
        @csrf
        @method('PATCH')

        <div class="space-y-3" x-show="tab === 'file'" x-cloak>
            <input type="hidden" name="content_mode" value="file_upload" :disabled="tab !== 'file'">
            <div>
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Dosya</label>
                <input type="file" name="file" accept=".doc,.docx,.pdf,.txt,.rtf" class="block w-full rounded-xl border border-ink/10 bg-white text-sm text-ink file:me-3 file:rounded-lg file:border-0 file:bg-ink file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white" :disabled="tab !== 'file'">
                <p class="mt-1.5 text-[11px] text-ink-3">.doc, .docx, .txt, .rar, .zip — en fazla 20MB</p>
                @if (! empty($payload['file_path']))
                    <p class="mt-1.5 text-[11px] text-ink-3">Yüklü: {{ basename($payload['file_path']) }}</p>
                @endif
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Görsel</label>
                <input type="file" name="image" accept=".png,.jpg,.jpeg" class="block w-full rounded-xl border border-ink/10 bg-white text-sm text-ink file:me-3 file:rounded-lg file:border-0 file:bg-ink file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white" :disabled="tab !== 'file'">
                <p class="mt-1.5 text-[11px] text-ink-3">.png, .jpg, .jpeg — en fazla 20MB, opsiyonel</p>
                @if (! empty($payload['image_path']))
                    <p class="mt-1.5 text-[11px] text-ink-3">Yüklü: {{ basename($payload['image_path']) }}</p>
                @endif
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Yayın Zamanı</label>
                <input type="date" name="publish_at" value="{{ old('publish_at', $payload['publish_at'] ?? '') }}" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" :disabled="tab !== 'file'">
                <p class="mt-1.5 text-[11px] text-ink-3">En yakın tarih için boş bırakın.</p>
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Not</label>
                <textarea name="note" rows="2" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" placeholder="Sipariş ile ilgili eklemek istediğiniz not (opsiyonel)" :disabled="tab !== 'file'">{{ old('note', $payload['note'] ?? '') }}</textarea>
            </div>
        </div>

        <div class="space-y-3" x-show="tab === 'ai'" x-cloak>
            <input type="hidden" name="content_mode" value="ai_article" :disabled="tab !== 'ai'">
            <div>
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Makale Seçim <span class="text-brand-500">*</span></label>
                <select name="article_word_package_id" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" :disabled="tab !== 'ai'">
                    <option value="">Makale seçin</option>
                    @foreach ($wordPackages as $package)
                        <option value="{{ $package->id }}" @selected((int) old('article_word_package_id', $item->article_word_package_id) === (int) $package->id)>
                            {{ $package->word_count }} kelime — {{ number_format((float) $package->price, 2, ',', '.') }} ₺
                        </option>
                    @endforeach
                </select>
                <p class="mt-1.5 text-[11px] text-ink-3">Zorunlu — ücret seçtiğiniz kelime sayısına göre belirlenir.</p>
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Site Adresi</label>
                <input type="url" name="target_url" value="{{ old('target_url', $payload['target_url'] ?? '') }}" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" placeholder="https://siteniz.com" :disabled="tab !== 'ai'">
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Anahtar Kelimeler</label>
                <input type="text" name="keywords" value="{{ old('keywords', $payload['keywords'] ?? '') }}" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" placeholder="max 3, virgülle ayır" :disabled="tab !== 'ai'">
                <p class="mt-1.5 text-[11px] text-ink-3">En fazla 3 anahtar kelime, virgülle ayırın.</p>
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Brief / Notlar</label>
                <textarea name="brief" rows="3" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" placeholder="Vurgulamak istediğiniz mesaj, ton, marka bilgisi vs." :disabled="tab !== 'ai'">{{ old('brief', $payload['brief'] ?? '') }}</textarea>
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Yayın Zamanı</label>
                <input type="date" name="publish_at" value="{{ old('publish_at', $payload['publish_at'] ?? '') }}" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" :disabled="tab !== 'ai'">
                <p class="mt-1.5 text-[11px] text-ink-3">En yakın tarih için boş bırakın.</p>
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Not</label>
                <textarea name="note" rows="2" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0" placeholder="Sipariş ile ilgili eklemek istediğiniz not (opsiyonel)" :disabled="tab !== 'ai'">{{ old('note', $payload['note'] ?? '') }}</textarea>
            </div>
        </div>

        <button type="submit" class="inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]">
            Seçimleri Kaydet
        </button>
    </form>
</div>
