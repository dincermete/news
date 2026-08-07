{{-- Social-proof toast; bottom-start so it does not block chatbot (bottom-end). --}}
<div
    class="mobile-floating-widget pointer-events-none fixed bottom-[calc(4.5rem+env(safe-area-inset-bottom))] start-4 z-40 w-[min(100%-2rem,22rem)] xl:bottom-4"
    x-data="fakeOrderToast(@js(url('/api/fake-notification')))"
    x-cloak
>
    <div
        x-show="visible"
        x-transition:enter="transition ease-out duration-400"
        x-transition:enter-start="-translate-x-3 translate-y-2 scale-[0.96] opacity-0"
        x-transition:enter-end="translate-x-0 translate-y-0 scale-100 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="-translate-x-2 opacity-0"
        class="fake-order-toast pointer-events-auto overflow-hidden rounded-2xl border border-ink/10 bg-white shadow-pop"
        :class="visible && 'is-playing'"
        role="status"
    >
        <div class="flex items-start gap-3 p-3.5 pe-3">
            <div class="relative shrink-0">
                <div
                    class="inline-flex size-11 items-center justify-center rounded-xl bg-gradient-to-b from-black to-[#363b3c] text-sm font-semibold tracking-wide text-white"
                    x-text="initials"
                    aria-hidden="true"
                ></div>
                <span class="absolute -bottom-0.5 -end-0.5 flex size-3.5 items-center justify-center rounded-full bg-emerald-500 ring-2 ring-white" aria-hidden="true">
                    <svg class="size-2 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd"/></svg>
                </span>
            </div>

            <div class="min-w-0 flex-1 pt-0.5">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-ink" x-text="name"></p>
                        <p class="mt-0.5 flex items-center gap-1.5 text-[11px] font-medium text-ink-3">
                            <span class="inline-flex items-center gap-1 truncate">
                                <svg class="size-3 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                <span class="truncate" x-text="city"></span>
                            </span>
                            <span class="size-0.5 shrink-0 rounded-full bg-ink-3/50" aria-hidden="true"></span>
                            <span class="shrink-0 inline-flex items-center gap-1 text-emerald-600">
                                <span class="relative flex size-1.5">
                                    <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                                    <span class="relative inline-flex size-1.5 rounded-full bg-emerald-500"></span>
                                </span>
                                Az önce
                            </span>
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex size-7 shrink-0 items-center justify-center rounded-full text-ink-3 transition hover:bg-paper hover:text-ink"
                        @click="dismiss()"
                        aria-label="Kapat"
                    >
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <p class="mt-2 text-[13px] font-medium leading-snug text-ink-2">
                    <span class="text-ink">Sipariş verdi:</span>
                    <span x-text="product"></span>
                </p>
            </div>
        </div>

        <div class="h-0.5 w-full bg-paper-2" aria-hidden="true">
            <div class="fake-order-toast__progress h-full origin-left bg-gradient-to-r from-accent-500 to-brand-500"></div>
        </div>
    </div>
</div>
