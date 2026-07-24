@php
    $tileIcon = 'inline-flex size-10 items-center justify-center rounded-xl';
@endphp
<div class="grid gap-3 {{ ($hasWalletTopupItem ?? false) ? 'sm:grid-cols-2' : 'sm:grid-cols-3' }}">
    <button
        type="button"
        @click="if (!locked) tab = 'card'"
        class="rounded-2xl border-2 p-4 text-left transition"
        :class="tab === 'card' ? 'border-accent-600 bg-accent-50/40' : (locked ? 'border-ink/10 bg-white opacity-50' : 'border-ink/10 bg-white hover:border-ink/25')"
    >
        <span class="{{ $tileIcon }}" :class="tab === 'card' ? 'bg-accent-600 text-white' : 'bg-paper text-ink-2'">
            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3M3.75 6h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z"/></svg>
        </span>
        <span class="mt-2.5 block text-sm font-semibold text-ink">Kredi / Banka Kartı</span>
        <span class="mt-0.5 block text-xs text-ink-3">Tek çekim veya 9 aya kadar taksit</span>
    </button>

    <button
        type="button"
        @click="if (!locked) tab = 'bank_transfer'"
        class="rounded-2xl border-2 p-4 text-left transition"
        :class="tab === 'bank_transfer' ? 'border-accent-600 bg-accent-50/40' : (locked ? 'border-ink/10 bg-white opacity-50' : 'border-ink/10 bg-white hover:border-ink/25')"
    >
        <span class="{{ $tileIcon }}" :class="tab === 'bank_transfer' ? 'bg-accent-600 text-white' : 'bg-paper text-ink-2'">
            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5M3 21V9.75L12 3l9 6.75V21M9.75 21v-6a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v6"/></svg>
        </span>
        <span class="mt-2.5 block text-sm font-semibold text-ink">Havale / EFT</span>
        <span class="mt-0.5 block text-xs text-ink-3">Banka hesaplarımıza %{{ rtrim(rtrim(number_format($bankTransferDiscountPercent, 2, '.', ''), '0'), '.') }} indirimli</span>
    </button>

    @unless ($hasWalletTopupItem ?? false)
        <button
            type="button"
            @click="if (!locked) tab = 'balance'"
            class="rounded-2xl border-2 p-4 text-left transition"
            :class="tab === 'balance' ? 'border-accent-600 bg-accent-50/40' : (locked ? 'border-ink/10 bg-white opacity-50' : 'border-ink/10 bg-white hover:border-ink/25')"
        >
            <span class="{{ $tileIcon }}" :class="tab === 'balance' ? 'bg-accent-600 text-white' : 'bg-paper text-ink-2'">
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m11.48 3.499 1.912 5.813a1 1 0 0 0 .95.69h6.116c.969 0 1.371 1.24.588 1.81l-4.948 3.593a1 1 0 0 0-.363 1.118l1.911 5.813c.3.916-.755 1.688-1.539 1.118l-4.947-3.593a1 1 0 0 0-1.176 0l-4.947 3.593c-.784.57-1.838-.202-1.539-1.118l1.911-5.813a1 1 0 0 0-.363-1.118l-4.947-3.593c-.784-.57-.38-1.81.588-1.81h6.117a1 1 0 0 0 .95-.69l1.911-5.813Z"/></svg>
            </span>
            <span class="mt-2.5 block text-sm font-semibold text-ink">Bakiye ile Öde</span>
            <span class="mt-0.5 block text-xs text-ink-3">Mevcut bakiyenizden düş</span>
        </button>
    @endunless
</div>
