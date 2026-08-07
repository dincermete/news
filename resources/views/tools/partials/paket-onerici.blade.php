@php
    $optionBtn = 'w-full rounded-xl border px-4 py-3 text-start text-sm font-medium transition';
    $btnDark = 'group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 text-sm font-medium text-white transition hover:scale-[1.02] active:scale-[0.98]';
@endphp

<div
    x-data="{
        goal: null,
        budget: null,
        content: null,
        recommendations: {{ \Illuminate\Support\Js::from([
            'hiz' => ['name' => 'Footer Link', 'url' => route('footer-links.index'), 'text' => 'En hızlı ve en düşük bütçeli seçenek: mevcut içeriğinize hemen link kazandırır.'],
            'otorite' => ['name' => 'Backlink Paketleri', 'url' => route('backlink-packages.index'), 'text' => 'Uzun vadeli domain otoritesi için tasarlanmış, rekabet seviyesine göre paketler.'],
            'bilinirlik-yuksek' => ['name' => 'Basın Bülteni', 'url' => route('press-release.index'), 'text' => 'Marka bilinirliğini geniş kitlelere en hızlı duyuran, yüksek bütçeye uygun format.'],
            'bilinirlik-orta' => ['name' => 'Tanıtım Paketleri', 'url' => route('bundles.index'), 'text' => 'Birden çok haber sitesinde tanıtım yazısı ile marka bilinirliğini dengeli bir bütçeyle artırır.'],
            'surekli' => ['name' => 'SEO Paketleri', 'url' => route('seo-packages.index'), 'text' => 'Sürekli, aylık bazlı SEO çalışması isteyenler için anahtar kelime bazlı paketler.'],
        ]) }},
        get key() {
            if (! this.goal || ! this.budget) {
                return null;
            }
            if (this.goal === 'hiz') {
                return this.budget === 'dusuk' ? 'hiz' : 'otorite';
            }
            if (this.goal === 'otorite') {
                return this.content === 'yardim' ? 'surekli' : 'otorite';
            }
            if (this.goal === 'bilinirlik') {
                return this.budget === 'yuksek' ? 'bilinirlik-yuksek' : 'bilinirlik-orta';
            }
            return 'otorite';
        },
        get result() {
            return this.key ? this.recommendations[this.key] : null;
        },
        reset() {
            this.goal = null;
            this.budget = null;
            this.content = null;
        },
    }"
    class="space-y-6"
>
    <div>
        <p class="mb-2 text-sm font-semibold text-ink">1. Öncelikli hedefiniz nedir?</p>
        <div class="grid gap-2 sm:grid-cols-3">
            <button type="button" class="{{ $optionBtn }}" :class="goal === 'hiz' ? 'border-ink bg-ink text-white' : 'border-ink/10 bg-white text-ink hover:border-ink/25'" @click="goal = 'hiz'">Hızlı görünürlük</button>
            <button type="button" class="{{ $optionBtn }}" :class="goal === 'otorite' ? 'border-ink bg-ink text-white' : 'border-ink/10 bg-white text-ink hover:border-ink/25'" @click="goal = 'otorite'">Uzun vadeli otorite</button>
            <button type="button" class="{{ $optionBtn }}" :class="goal === 'bilinirlik' ? 'border-ink bg-ink text-white' : 'border-ink/10 bg-white text-ink hover:border-ink/25'" @click="goal = 'bilinirlik'">Marka bilinirliği</button>
        </div>
    </div>

    <div x-show="goal" x-cloak>
        <p class="mb-2 text-sm font-semibold text-ink">2. Bütçe aralığınız?</p>
        <div class="grid gap-2 sm:grid-cols-3">
            <button type="button" class="{{ $optionBtn }}" :class="budget === 'dusuk' ? 'border-ink bg-ink text-white' : 'border-ink/10 bg-white text-ink hover:border-ink/25'" @click="budget = 'dusuk'">Düşük</button>
            <button type="button" class="{{ $optionBtn }}" :class="budget === 'orta' ? 'border-ink bg-ink text-white' : 'border-ink/10 bg-white text-ink hover:border-ink/25'" @click="budget = 'orta'">Orta</button>
            <button type="button" class="{{ $optionBtn }}" :class="budget === 'yuksek' ? 'border-ink bg-ink text-white' : 'border-ink/10 bg-white text-ink hover:border-ink/25'" @click="budget = 'yuksek'">Yüksek</button>
        </div>
    </div>

    <div x-show="goal === 'otorite' && budget" x-cloak>
        <p class="mb-2 text-sm font-semibold text-ink">3. İçeriğiniz hazır mı?</p>
        <div class="grid gap-2 sm:grid-cols-2">
            <button type="button" class="{{ $optionBtn }}" :class="content === 'hazir' ? 'border-ink bg-ink text-white' : 'border-ink/10 bg-white text-ink hover:border-ink/25'" @click="content = 'hazir'">Evet, hazır</button>
            <button type="button" class="{{ $optionBtn }}" :class="content === 'yardim' ? 'border-ink bg-ink text-white' : 'border-ink/10 bg-white text-ink hover:border-ink/25'" @click="content = 'yardim'">Yardım istiyorum</button>
        </div>
    </div>

    <template x-if="result">
        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Size önerimiz</p>
            <p class="mt-2 font-display text-xl font-semibold text-ink" x-text="result.name"></p>
            <p class="mt-1.5 text-sm text-ink-2" x-text="result.text"></p>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <a :href="result.url" class="{{ $btnDark }}">
                    <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                        <svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </span>
                    <span x-text="result.name + ' sayfasına git'"></span>
                </a>
                <button type="button" class="text-sm font-medium text-ink-3 hover:text-ink" @click="reset()">Baştan başla</button>
            </div>
        </div>
    </template>

    <p class="text-xs text-ink-3">Öneri, cevaplarınıza dayalı basit bir eşleştirmedir; bir başlangıç noktasıdır, tüm kataloğu dilediğiniz gibi inceleyebilirsiniz.</p>
</div>
