{{-- Social-proof toast; bottom-start so it does not block chatbot (bottom-end). --}}
<div
    class="pointer-events-none fixed bottom-4 start-4 z-40 max-w-xs"
    x-data="fakeOrderToast(@js(url('/api/fake-notification')))"
    x-cloak
>
    <div
        x-show="visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="pointer-events-auto flex items-start gap-2 rounded-md border border-zinc-200 bg-white p-3 shadow-lg"
        role="status"
    >
        <span class="mt-0.5 inline-flex size-8 shrink-0 items-center justify-center rounded-full bg-teal-50 text-teal-700">
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-sm text-zinc-800" x-text="message"></p>
            <p class="mt-1 text-[11px] text-zinc-400">Az önce</p>
        </div>
        <button type="button" class="shrink-0 rounded p-0.5 text-zinc-400 hover:text-zinc-700" @click="dismiss()" aria-label="Kapat">
            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        </button>
    </div>
</div>
