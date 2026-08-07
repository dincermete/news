@php
    $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
    $btnDark = 'shrink-0 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60';
@endphp

<div
    x-data="{
        domain: '',
        loading: false,
        error: '',
        result: null,
        checkUrl: @js(route('tools.ai-crawler-check')),
        csrf: @js(csrf_token()),
        toneFor(status) {
            return { allowed: 'bg-emerald-100 text-emerald-700', partial: 'bg-yellow-100 text-yellow-700', blocked: 'bg-brand-100 text-brand-700' }[status] || 'bg-ink/5 text-ink-3';
        },
        labelFor(status) {
            return { allowed: 'İzinli', partial: 'Kısmen engelli', blocked: 'Engelli' }[status] || status;
        },
        async check() {
            if (! this.domain.trim() || this.loading) {
                return;
            }
            this.loading = true;
            this.error = '';
            this.result = null;
            try {
                const response = await fetch(this.checkUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ domain: this.domain }),
                });
                const data = await response.json();
                if (! response.ok) {
                    this.error = data.message || 'Kontrol başarısız oldu.';
                    return;
                }
                this.result = data;
            } catch (e) {
                this.error = 'Bağlantı hatası. Lütfen tekrar deneyin.';
            } finally {
                this.loading = false;
            }
        },
    }"
    class="space-y-6"
>
    <form class="flex flex-col gap-2 sm:flex-row" @submit.prevent="check()">
        <div class="flex-1">
            <label class="{{ $label }}">Domain</label>
            <input type="text" x-model="domain" placeholder="siteniz.com" class="{{ $input }}">
        </div>
        <button type="submit" class="{{ $btnDark }} sm:mt-[26px]" :disabled="loading">
            <span x-show="!loading">Kontrol et</span>
            <span x-show="loading" x-cloak>Kontrol ediliyor…</span>
        </button>
    </form>

    <p class="text-sm text-brand-600" x-show="error" x-text="error" x-cloak></p>

    <template x-if="result">
        <div class="space-y-3">
            <p class="text-xs text-ink-3" x-show="!result.robots_found">
                <span class="font-semibold text-ink-2" x-text="result.domain"></span> için robots.txt bulunamadı; varsayılan olarak tüm botlara açık kabul edilir.
            </p>
            <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
                <template x-for="bot in result.bots" :key="bot.label">
                    <div class="flex items-center justify-between gap-3 border-b border-ink/5 px-5 py-3.5 last:border-b-0">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-ink" x-text="bot.label"></p>
                            <p class="mt-0.5 text-xs text-ink-3" x-text="bot.reason"></p>
                        </div>
                        <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="toneFor(bot.status)" x-text="labelFor(bot.status)"></span>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <p class="text-xs text-ink-3">GPTBot, Google-Extended, ClaudeBot, PerplexityBot, CCBot ve Applebot-Extended gibi bilinen büyük AI/veri botlarının robots.txt üzerindeki erişim durumunu kontrol eder.</p>
</div>
