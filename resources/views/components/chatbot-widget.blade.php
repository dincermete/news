{{--
    Lazy-mounted chatbot: Alpine data registered in chatbot.js after idle.
    Intercom-style Messenger: Home / Messages / Help / News tabs + a separate conversation screen.
--}}
@php
    $icons = [
        'sparkle' => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z',
        'close' => 'M6 18 18 6M6 6l12 12',
        'back' => 'M15.75 19.5 8.25 12l7.5-7.5',
        'chevron' => 'm8.25 4.5 7.5 7.5-7.5 7.5',
        'home' => 'M2.25 21h19.5M3 21V9.75L12 3l9 6.75V21M9.75 21v-6a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v6',
        'chat' => 'M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z',
        'bell' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0',
        'send' => 'M6 12 3.269 3.126A59.768 59.768 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.876L5.999 12Zm0 0h7.5',
    ];

    $brandName = $siteSettings->siteName() ?? 'Tanıtım Yazısı';
    $faqs = $chatbotFaqs ?? collect();
    $announcements = $chatbotAnnouncements ?? collect();
    $greetingName = $chatbotUserFirstName ?? null;

    $tabBtn = 'flex flex-1 flex-col items-center gap-1 py-2.5 text-[10px] font-medium transition';
@endphp

<div
    id="chatbot-widget"
    class="pointer-events-none fixed inset-x-0 bottom-0 z-50 flex flex-col items-end px-4 pb-4 sm:inset-x-auto sm:end-4 sm:w-[25rem]"
    x-ignore
    data-chatbot-endpoint="{{ route('chatbot.message') }}"
    data-chatbot-conversation-endpoint="{{ route('chatbot.conversation') }}"
    data-chatbot-csrf="{{ csrf_token() }}"
    aria-live="polite"
>
    {{-- Panel --}}
    <div
        class="pointer-events-auto mb-3 hidden h-[min(70vh,640px)] w-full max-w-[23rem] flex-col overflow-hidden rounded-[20px] bg-panel opacity-0 [transform-origin:bottom_right]"
        style="box-shadow: 0 24px 70px -12px rgb(0 0 0 / 0.5), 0 8px 24px -8px rgb(0 0 0 / 0.35);"
        data-chatbot-panel
        role="dialog"
        aria-label="{{ $brandName }} sohbet penceresi"
    >
        {{-- ============== HOME ============== --}}
        <div class="flex flex-1 flex-col overflow-hidden" data-chatbot-screen="home">
            <div class="panel-dark relative shrink-0 px-5 pb-6 pt-5 text-white">
                <div class="relative flex items-center justify-between">
                    <span class="relative inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-white/15">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['sparkle'] }}"/></svg>
                        <span class="absolute -end-0.5 -bottom-0.5 inline-flex size-3 items-center justify-center">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex size-2.5 rounded-full border-2 border-panel bg-emerald-400"></span>
                        </span>
                    </span>
                    <button type="button" class="shrink-0 rounded-full p-1.5 text-white/70 transition hover:bg-white/10 hover:text-white" data-chatbot-close aria-label="Sohbeti kapat">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['close'] }}"/></svg>
                    </button>
                </div>

                <p class="relative mt-5 text-sm text-white/60">Merhaba{{ $greetingName ? ' '.$greetingName : '' }}.</p>
                <p class="relative font-display text-2xl font-semibold leading-snug">Size nasıl yardımcı olabiliriz?</p>
            </div>

            <div class="chatbot-scroll flex-1 space-y-2.5 overflow-y-auto px-4 py-4">
                {{-- Recent conversation card, shown by JS when a prior message exists --}}
                <button
                    type="button"
                    class="hidden w-full items-start gap-3 rounded-2xl border border-white/10 bg-white/5 p-3.5 text-start transition hover:bg-white/10"
                    data-chatbot-recent-card
                    data-chatbot-open-conversation
                >
                    <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent-500 to-brand-500 text-white">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['sparkle'] }}"/></svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-white">{{ $brandName }} Asistan</span>
                            <span class="shrink-0 text-[10px] text-white/40" data-chatbot-recent-time></span>
                        </span>
                        <span class="mt-0.5 block truncate text-[13px] text-white/60" data-chatbot-recent-text></span>
                    </span>
                </button>

                <button
                    type="button"
                    class="group flex w-full items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/5 p-3.5 text-start transition hover:bg-white/10"
                    data-chatbot-start-conversation
                >
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-white">Yeni sohbet başlat</span>
                        <span class="mt-0.5 block text-[12px] text-white/50">Bütçenize uygun plan veya site önerisi alın</span>
                    </span>
                    <svg class="size-4 shrink-0 text-white/40 transition group-hover:translate-x-0.5 group-hover:text-white/70" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['chevron'] }}"/></svg>
                </button>

                @if ($announcements->isNotEmpty())
                    <button
                        type="button"
                        class="w-full rounded-2xl border border-white/10 bg-gradient-to-br from-accent-700/50 to-brand-700/40 p-4 text-start transition hover:from-accent-700/60 hover:to-brand-700/50"
                        data-chatbot-tab-target="news"
                    >
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-white/60">Duyuru</p>
                        <p class="mt-1 text-sm font-semibold text-white">{{ $announcements->first()->title }}</p>
                        <p class="mt-1 line-clamp-2 text-[12px] leading-relaxed text-white/70">{{ $announcements->first()->body }}</p>
                    </button>
                @endif

                @if ($faqs->isNotEmpty())
                    <div>
                        <p class="px-1 pb-2 text-[10px] font-semibold uppercase tracking-wide text-white/40">Sık sorulanlar</p>
                        <div class="space-y-1.5">
                            @foreach ($faqs->take(3) as $faq)
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between gap-2 rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-start text-[13px] text-white/80 transition hover:bg-white/10"
                                    data-chip="{{ $faq->question_topic }}"
                                >
                                    <span class="truncate">{{ $faq->question_topic }}</span>
                                    <svg class="size-3.5 shrink-0 text-white/30" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['chevron'] }}"/></svg>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============== CONVERSATION ============== --}}
        <div class="hidden flex-1 flex-col overflow-hidden" data-chatbot-screen="conversation">
            <div class="flex shrink-0 items-center gap-2.5 border-b border-white/10 px-3 py-3">
                <button type="button" class="shrink-0 rounded-full p-1.5 text-white/70 transition hover:bg-white/10 hover:text-white" data-chatbot-back aria-label="Geri">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['back'] }}"/></svg>
                </button>
                <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent-500 to-brand-500 text-white">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['sparkle'] }}"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-white">{{ $brandName }} Asistan</p>
                    <p class="truncate text-[11px] text-white/50">Genellikle hemen yanıtlıyoruz</p>
                </div>
                <button type="button" class="shrink-0 rounded-full p-1.5 text-white/70 transition hover:bg-white/10 hover:text-white" data-chatbot-close aria-label="Sohbeti kapat">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['close'] }}"/></svg>
                </button>
            </div>

            <div class="chatbot-scroll flex flex-1 flex-col gap-3 overflow-y-auto px-4 py-4" data-chatbot-messages>
                <div class="flex items-end gap-2" data-chatbot-welcome>
                    <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent-500 to-brand-500 text-white">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['sparkle'] }}"/></svg>
                    </span>
                    <div class="max-w-[85%] rounded-2xl rounded-bl-sm bg-white/10 px-3.5 py-2.5 text-[13px] leading-relaxed text-white">
                        Merhaba! Bütçenize uygun plan veya site önerisi için yazın, ya da hazır sorulardan birini seçin.
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 flex-wrap gap-1.5 border-t border-white/10 px-4 py-3" data-chatbot-chips>
                <button type="button" class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1.5 text-[11px] font-medium text-white/70 transition hover:border-white/25 hover:bg-white/10 hover:text-white" data-chip="5000 TL'ye plan çıkar">5000 TL'ye plan çıkar</button>
                <button type="button" class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1.5 text-[11px] font-medium text-white/70 transition hover:border-white/25 hover:bg-white/10 hover:text-white" data-chip="En uygun siteler hangileri?">En uygun siteler</button>
                <button type="button" class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1.5 text-[11px] font-medium text-white/70 transition hover:border-white/25 hover:bg-white/10 hover:text-white" data-chip="Backlink nedir?">Backlink nedir?</button>
                <button type="button" class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1.5 text-[11px] font-medium text-white/70 transition hover:border-white/25 hover:bg-white/10 hover:text-white" data-chip="Destek ile konuşmak istiyorum">Destek istiyorum</button>
            </div>

            <form class="flex shrink-0 items-center gap-2 border-t border-white/10 p-3" data-chatbot-form>
                <input
                    type="text"
                    name="message"
                    maxlength="4000"
                    placeholder="Mesajınızı yazın…"
                    class="block w-full rounded-full border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder:text-white/35 focus:border-white/25 focus:bg-white/10 focus:ring-0"
                    data-chatbot-input
                    autocomplete="off"
                >
                <button
                    type="submit"
                    class="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent-600 to-brand-600 text-white transition hover:scale-105 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:scale-100"
                    data-chatbot-send
                    aria-label="Gönder"
                >
                    <svg class="size-4 rtl:-scale-x-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['send'] }}"/></svg>
                </button>
            </form>
        </div>

        {{-- ============== MESSAGES ============== --}}
        <div class="hidden flex-1 flex-col overflow-hidden" data-chatbot-screen="messages">
            <div class="shrink-0 px-5 pb-4 pt-5">
                <p class="font-display text-lg font-semibold text-white">Mesajlar</p>
            </div>
            <div class="chatbot-scroll flex-1 overflow-y-auto px-4 pb-4">
                <button
                    type="button"
                    class="hidden w-full items-start gap-3 rounded-2xl border border-white/10 bg-white/5 p-3.5 text-start transition hover:bg-white/10"
                    data-chatbot-messages-card
                    data-chatbot-open-conversation
                >
                    <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent-500 to-brand-500 text-white">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['sparkle'] }}"/></svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-white">{{ $brandName }} Asistan</span>
                            <span class="shrink-0 text-[10px] text-white/40" data-chatbot-messages-time></span>
                        </span>
                        <span class="mt-0.5 block truncate text-[13px] text-white/60" data-chatbot-messages-text></span>
                    </span>
                </button>

                <div class="flex flex-col items-center px-4 py-14 text-center" data-chatbot-messages-empty>
                    <span class="inline-flex size-12 items-center justify-center rounded-2xl bg-white/5 text-white/40">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['chat'] }}"/></svg>
                    </span>
                    <p class="mt-3 text-sm font-semibold text-white">Henüz mesajınız yok</p>
                    <p class="mt-1 text-xs text-white/50">Bir sohbet başlatın, buradan takip edin.</p>
                    <button type="button" class="mt-4 rounded-full bg-gradient-to-br from-accent-600 to-brand-600 px-4 py-2 text-xs font-semibold text-white transition hover:scale-105" data-chatbot-start-conversation>
                        Yeni sohbet başlat
                    </button>
                </div>
            </div>
        </div>

        {{-- ============== HELP ============== --}}
        <div class="hidden flex-1 flex-col overflow-hidden" data-chatbot-screen="help">
            <div class="shrink-0 px-5 pb-4 pt-5">
                <p class="font-display text-lg font-semibold text-white">Yardım</p>
                <p class="mt-0.5 text-xs text-white/50">Sık sorulan sorular</p>
            </div>
            <div class="chatbot-scroll flex-1 space-y-2 overflow-y-auto px-4 pb-4">
                @forelse ($faqs as $faq)
                    <div class="rounded-2xl border border-white/10 bg-white/5">
                        <button type="button" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-start" data-chatbot-faq-toggle aria-expanded="false">
                            <span class="text-[13px] font-medium text-white">{{ $faq->question_topic }}</span>
                            <svg class="size-3.5 shrink-0 text-white/40 transition" data-chatbot-faq-chevron xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['chevron'] }}"/></svg>
                        </button>
                        <div class="hidden px-4 pb-3.5 text-[12px] leading-relaxed text-white/60" data-chatbot-faq-panel>
                            {{ $faq->answer }}
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center px-4 py-14 text-center">
                        <span class="inline-flex size-12 items-center justify-center rounded-2xl bg-white/5 text-white/40">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 9.879a3 3 0 1 1 3.414 4.821c-.735.51-1.293 1.235-1.293 2.1M12 17.25h.008v.008H12v-.008Z"/></svg>
                        </span>
                        <p class="mt-3 text-sm font-semibold text-white">Henüz yardım makalesi yok</p>
                        <p class="mt-1 text-xs text-white/50">Sorunuzu sohbete yazabilirsiniz.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ============== NEWS ============== --}}
        <div class="hidden flex-1 flex-col overflow-hidden" data-chatbot-screen="news">
            <div class="shrink-0 px-5 pb-4 pt-5">
                <p class="font-display text-lg font-semibold text-white">Duyurular</p>
            </div>
            <div class="chatbot-scroll flex-1 space-y-2.5 overflow-y-auto px-4 pb-4">
                @forelse ($announcements as $announcement)
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-white/10 text-white/70">
                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['bell'] }}"/></svg>
                            </span>
                            <span class="text-[10px] text-white/40">{{ $announcement->created_at?->diffForHumans() }}</span>
                        </div>
                        <p class="mt-2 text-sm font-semibold text-white">{{ $announcement->title }}</p>
                        <p class="mt-1 text-[12px] leading-relaxed text-white/60">{{ $announcement->body }}</p>
                    </div>
                @empty
                    <div class="flex flex-col items-center px-4 py-14 text-center">
                        <span class="inline-flex size-12 items-center justify-center rounded-2xl bg-white/5 text-white/40">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['bell'] }}"/></svg>
                        </span>
                        <p class="mt-3 text-sm font-semibold text-white">Şu anda duyuru yok</p>
                        <p class="mt-1 text-xs text-white/50">Yeni duyurular burada görünecek.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ============== TAB BAR ============== --}}
        <div class="flex shrink-0 border-t border-white/10" data-chatbot-tabbar>
            <button type="button" class="{{ $tabBtn }} text-white" data-chatbot-tab="home" data-chatbot-tab-active="true">
                <svg class="size-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['home'] }}"/></svg>
                Ana Sayfa
            </button>
            <button type="button" class="{{ $tabBtn }} text-white/45" data-chatbot-tab="messages">
                <svg class="size-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['chat'] }}"/></svg>
                Mesajlar
            </button>
            <button type="button" class="{{ $tabBtn }} text-white/45" data-chatbot-tab="help">
                <svg class="size-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 9.879a3 3 0 1 1 3.414 4.821c-.735.51-1.293 1.235-1.293 2.1M12 17.25h.008v.008H12v-.008Z"/></svg>
                Yardım
            </button>
            <button type="button" class="{{ $tabBtn }} relative text-white/45" data-chatbot-tab="news">
                <svg class="size-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['bell'] }}"/></svg>
                Duyurular
                @if ($announcements->isNotEmpty())
                    <span class="absolute end-[27%] top-1.5 size-1.5 rounded-full bg-brand-500"></span>
                @endif
            </button>
        </div>
    </div>

    {{-- Launcher --}}
    <button
        type="button"
        class="pointer-events-auto relative inline-flex size-14 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-ink to-panel text-white transition hover:scale-105 active:scale-95"
        style="box-shadow: 0 12px 30px -6px rgb(237 31 32 / 0.45), 0 4px 12px -4px rgb(0 0 0 / 0.4);"
        data-chatbot-toggle
        aria-expanded="false"
        aria-controls="chatbot-widget"
    >
        <span class="sr-only">Sohbet başlat</span>
        <svg class="size-6 transition-all duration-200" data-chatbot-icon-chat xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['chat'] }}"/>
        </svg>
        <svg class="absolute size-6 rotate-45 scale-0 opacity-0 transition-all duration-200" data-chatbot-icon-close xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        <span class="absolute -end-0.5 -top-0.5 hidden size-4 items-center justify-center rounded-full bg-brand-500 text-[9px] font-bold text-white ring-2 ring-white" data-chatbot-badge>1</span>
    </button>
</div>
