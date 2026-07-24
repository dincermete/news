<div
    x-data="loginRequiredModal()"
    x-show="open"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-[70] flex items-center justify-center bg-ink/40 p-4 backdrop-blur-sm"
    @keydown.escape.window="close()"
    role="dialog"
    aria-modal="true"
    aria-label="Giriş gerekli"
>
    <div class="w-full max-w-sm rounded-[20px] bg-white p-6 text-center shadow-pop" @click.outside="close()" x-transition>
        <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-teal-50 text-teal-600">
            <svg class="size-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11.25v5.25m0-8.25h.008v.008H12V8.25Z" />
            </svg>
        </div>
        <h2 class="mt-4 font-display text-lg font-semibold text-ink">Giriş gerekli</h2>
        <p class="mt-2 text-sm text-ink-2">Sepete ürün eklemek için önce hesabınıza giriş yapmalısınız.</p>
        <div class="mt-6 flex items-center gap-3">
            <button
                type="button"
                class="flex-1 rounded-xl border border-ink/10 bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:border-ink/25"
                @click="close()"
            >
                İptal
            </button>
            <a
                href="{{ route('login') }}"
                class="flex-1 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700"
            >
                Giriş yap
            </a>
        </div>
    </div>
</div>
