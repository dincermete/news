@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator<\App\Models\InstagramAccount> $accounts */
    $money = fn (float $amount, string $currency): string => number_format($amount, 2, ',', '.').($currency === 'TRY' ? '₺' : '$');
    $fmt = fn (int $n): string => number_format($n, 0, ',', '.');
@endphp

@section('content')
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto flex max-w-6xl flex-col items-center px-5 pb-10 pt-14 text-center sm:px-8 sm:pb-12 sm:pt-16">
                <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80">
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Story Satış</span>
                    {{ $accounts->total() }}+ hesap
                </p>
                <h1 class="mt-5 max-w-2xl font-display text-4xl font-medium leading-[1.12] sm:text-5xl">
                    Instagram Post ve Story ile markanızı duyurun
                </h1>
                <p class="mt-4 max-w-xl text-lg font-medium leading-relaxed text-white/65">
                    Seçtiğiniz Instagram hesabında Post veya Story olarak paylaşım alın.
                </p>

                <form method="get" action="{{ route('story.index') }}" class="mt-7 flex w-full max-w-xl items-center gap-2 rounded-full border border-white/15 bg-white p-1.5 shadow-pop" role="search">
                    <svg class="ms-3 size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <input type="search" name="q" value="{{ $q }}" placeholder="Örn. @markaadi" class="w-full border-0 bg-transparent p-0 py-2 text-sm text-ink placeholder:text-ink-3 focus:ring-0" aria-label="Instagram hesabı ara">
                    <button type="submit" class="inline-flex shrink-0 items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.03] active:scale-[0.98]">
                        Ara
                    </button>
                </form>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        @if ($accounts->isEmpty())
            <div class="rounded-[20px] border border-ink/10 bg-paper px-6 py-16 text-center">
                <p class="font-display text-lg font-semibold text-ink">Bu aramaya uygun Instagram hesabı bulunamadı.</p>
                <a href="{{ route('story.index') }}" class="mt-5 inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white">Filtreleri Sıfırla</a>
            </div>
        @else
            <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] border-collapse text-start">
                        <thead>
                            <tr class="border-b border-ink/10 bg-paper">
                                <th class="sticky start-0 z-10 bg-paper px-5 py-3.5 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Hesap</th>
                                <th class="px-4 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3">Takipçi</th>
                                <th class="px-5 py-3.5 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Fiyatlar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/5">
                            @foreach ($accounts as $account)
                                <tr class="group transition hover:bg-paper">
                                    <td class="sticky start-0 z-10 bg-white px-5 py-3.5 transition group-hover:bg-paper">
                                        <div class="flex items-center gap-x-3">
                                            <x-instagram-avatar :account="$account" :size="32" class="shrink-0" />
                                            <span class="min-w-0">
                                                <span class="block truncate text-sm font-semibold text-ink">{{ $account->handle }}</span>
                                                <span class="block truncate text-xs text-ink-3">{{ $account->name ?? 'Instagram hesabı' }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if ($account->follower_count !== null)
                                            <span class="inline-flex min-w-9 items-center justify-center rounded-lg bg-paper px-2 py-1 text-xs font-bold text-ink">{{ $fmt($account->follower_count) }}</span>
                                        @else
                                            <span class="text-xs text-ink-3">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            @foreach ($account->storyPrices as $storyPrice)
                                                <form method="post" action="{{ route('cart.add') }}" class="flex items-center gap-x-2 rounded-full border border-ink/10 bg-paper py-1 ps-3 pe-1">
                                                    @csrf
                                                    <input type="hidden" name="product_type" value="story">
                                                    <input type="hidden" name="instagram_account_id" value="{{ $account->id }}">
                                                    <input type="hidden" name="instagram_story_price_id" value="{{ $storyPrice->id }}">
                                                    <span class="text-xs text-ink-2">
                                                        <span class="font-semibold text-ink">{{ $storyPrice->format->getLabel() }}</span>
                                                        — {{ $money((float) $storyPrice->price, $storyPrice->currency?->value ?? 'TRY') }}
                                                    </span>
                                                    @guest
                                                        <button type="button" class="inline-flex items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-3 py-1.5 text-[11px] font-semibold text-white transition hover:scale-[1.04] active:scale-[0.98]" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">
                                                            Sepete Ekle
                                                        </button>
                                                    @else
                                                        <button type="submit" class="inline-flex items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-3 py-1.5 text-[11px] font-semibold text-white transition hover:scale-[1.04] active:scale-[0.98]">
                                                            Sepete Ekle
                                                        </button>
                                                    @endguest
                                                </form>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $accounts->links('vendor.pagination.storefront') }}
            </div>
        @endif
    </div>
@endsection
