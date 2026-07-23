{{-- Lazy-mounted chatbot: Alpine data registered in chatbot.js after idle. --}}
<div
    id="chatbot-widget"
    class="pointer-events-none fixed inset-x-0 bottom-0 z-50 flex flex-col items-end px-4 pb-4 sm:inset-x-auto sm:end-4"
    x-ignore
    data-chatbot-endpoint="{{ route('chatbot.message') }}"
    data-chatbot-csrf="{{ csrf_token() }}"
    aria-live="polite"
>
    {{-- Panel --}}
    <div
        class="pointer-events-auto mb-3 hidden w-full max-w-[23rem] flex-col overflow-hidden rounded-[20px] bg-white opacity-0 [transform-origin:bottom_right]"
        style="box-shadow: 0 24px 70px -12px rgb(10 11 11 / 0.35), 0 8px 24px -8px rgb(10 11 11 / 0.18);"
        data-chatbot-panel
        role="dialog"
        aria-label="NewsTanıtım Asistan sohbet penceresi"
    >
        {{-- Header --}}
        <div class="relative shrink-0 overflow-hidden bg-gradient-to-br from-accent-600 via-accent-700 to-brand-600 px-5 pb-6 pt-4 text-white">
            <div class="pointer-events-none absolute -end-10 -top-10 size-40 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>

            <div class="relative flex items-start justify-between gap-3">
                <div class="flex items-center gap-x-2.5">
                    <span class="relative inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-white/15">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                        <span class="absolute -end-0.5 -bottom-0.5 inline-flex size-3 items-center justify-center">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex size-2.5 rounded-full border-2 border-accent-600 bg-emerald-400"></span>
                        </span>
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold">NewsTanıtım Asistan</p>
                        <p class="truncate text-[11px] text-white/70">Genellikle hemen yanıtlıyoruz</p>
                    </div>
                </div>
                <button
                    type="button"
                    class="shrink-0 rounded-full p-1.5 text-white/80 transition hover:bg-white/15 hover:text-white"
                    data-chatbot-close
                    aria-label="Sohbeti kapat"
                >
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </button>
            </div>

            <p class="relative mt-4 font-display text-xl font-semibold leading-snug">Merhaba <span aria-hidden="true">👋</span></p>
            <p class="relative text-sm text-white/75">Size nasıl yardımcı olabiliriz?</p>
        </div>

        {{-- Messages --}}
        <div class="flex min-h-[240px] flex-1 flex-col gap-3 overflow-y-auto bg-paper px-4 py-4" data-chatbot-messages style="max-height: min(46vh, 360px);">
            <div class="flex items-end gap-2" data-chatbot-welcome>
                <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent-500 to-brand-500 text-white">
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                </span>
                <div class="max-w-[85%] rounded-2xl rounded-bl-sm bg-white px-3.5 py-2.5 text-[13px] leading-relaxed text-ink shadow-soft">
                    Merhaba! Bütçenize uygun plan veya site önerisi için yazın, ya da hazır sorulardan birini seçin.
                </div>
            </div>
        </div>

        {{-- Quick reply chips --}}
        <div class="flex flex-wrap gap-1.5 border-t border-ink/10 bg-white px-4 py-3" data-chatbot-chips>
            <button type="button" class="rounded-full border border-ink/10 bg-paper px-2.5 py-1.5 text-[11px] font-medium text-ink-2 transition hover:border-accent-300 hover:bg-accent-50 hover:text-accent-700" data-chip="5000 TL'ye plan çıkar">5000 TL'ye plan çıkar</button>
            <button type="button" class="rounded-full border border-ink/10 bg-paper px-2.5 py-1.5 text-[11px] font-medium text-ink-2 transition hover:border-accent-300 hover:bg-accent-50 hover:text-accent-700" data-chip="En uygun siteler hangileri?">En uygun siteler</button>
            <button type="button" class="rounded-full border border-ink/10 bg-paper px-2.5 py-1.5 text-[11px] font-medium text-ink-2 transition hover:border-accent-300 hover:bg-accent-50 hover:text-accent-700" data-chip="Backlink nedir?">Backlink nedir?</button>
            <button type="button" class="rounded-full border border-ink/10 bg-paper px-2.5 py-1.5 text-[11px] font-medium text-ink-2 transition hover:border-accent-300 hover:bg-accent-50 hover:text-accent-700" data-chip="Destek ile konuşmak istiyorum">Destek istiyorum</button>
        </div>

        {{-- Composer --}}
        <form class="flex shrink-0 items-center gap-2 border-t border-ink/10 bg-white p-3" data-chatbot-form>
            <input
                type="text"
                name="message"
                maxlength="4000"
                placeholder="Mesajınızı yazın…"
                class="block w-full rounded-full border border-ink/10 bg-paper px-4 py-2.5 text-sm text-ink placeholder:text-ink-3 focus:border-ink/25 focus:bg-white focus:ring-0"
                data-chatbot-input
                autocomplete="off"
            >
            <button
                type="submit"
                class="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent-600 to-brand-600 text-white transition hover:scale-105 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:scale-100"
                data-chatbot-send
                aria-label="Gönder"
            >
                <svg class="size-4 rtl:-scale-x-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.126A59.768 59.768 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.876L5.999 12Zm0 0h7.5"/></svg>
            </button>
        </form>
    </div>

    {{-- Launcher --}}
    <button
        type="button"
        class="pointer-events-auto relative inline-flex size-14 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent-600 to-brand-600 text-white transition hover:scale-105 active:scale-95"
        style="box-shadow: 0 12px 30px -6px rgb(237 31 32 / 0.5), 0 4px 12px -4px rgb(10 11 11 / 0.25);"
        data-chatbot-toggle
        aria-expanded="false"
        aria-controls="chatbot-widget"
    >
        <span class="sr-only">Sohbet başlat</span>
        <svg class="size-6 transition-all duration-200" data-chatbot-icon-chat xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/>
        </svg>
        <svg class="absolute size-6 rotate-45 scale-0 opacity-0 transition-all duration-200" data-chatbot-icon-close xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        <span class="absolute -end-0.5 -top-0.5 hidden size-4 items-center justify-center rounded-full bg-white text-[9px] font-bold text-accent-700 ring-2 ring-accent-600" data-chatbot-badge>1</span>
    </button>
</div>
