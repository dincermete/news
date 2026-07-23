{{-- Dismissible announcement strip; dismiss state in localStorage. --}}
@if (($headerAnnouncements ?? collect())->isNotEmpty())
    <div
        class="border-b border-teal-800 bg-teal-700 text-sm text-white"
        x-data="announcementBar(@js($headerAnnouncements->map(fn ($a) => ['id' => $a->id, 'title' => $a->title, 'body' => $a->body])->values()))"
        x-cloak
        x-show="visible.length > 0"
    >
        <template x-for="item in visible" :key="item.id">
            <div class="mx-auto flex max-w-6xl items-start justify-between gap-3 px-4 py-2 sm:px-6 lg:px-8">
                <div class="min-w-0">
                    <p class="font-semibold" x-text="item.title"></p>
                    <p class="mt-0.5 text-teal-50" x-text="item.body"></p>
                </div>
                <button
                    type="button"
                    class="shrink-0 rounded p-1 text-teal-100 hover:bg-teal-800 hover:text-white"
                    @click="dismiss(item.id)"
                    aria-label="Duyuruyu kapat"
                >
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </template>
    </div>
@endif
