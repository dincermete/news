@extends('layouts.account')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('content')
    @php
        $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    @endphp

    <div class="space-y-5">
        <div>
            <p><span class="{{ $chip }}">Genel bakış</span></p>
            <h1 class="mt-3 font-display text-2xl font-medium text-ink sm:text-[28px]">Dashboard</h1>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Toplam Bakiye</p>
                <p class="mt-2 font-display text-xl font-semibold text-ink">{{ number_format($totalBalance, 2, ',', '.') }} ₺</p>
            </div>
            <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Siparişler</p>
                <p class="mt-2 font-display text-xl font-semibold text-ink">{{ $orderCount }}</p>
                <p class="mt-1 text-xs text-ink-2">
                    @if ($latestOrder)
                        Son: #{{ $latestOrder->id }} — {{ $latestOrder->status?->getLabel() }}
                    @else
                        Henüz sipariş yok
                    @endif
                </p>
            </div>
            <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Çark Kazancı</p>
                <p class="mt-2 font-display text-xl font-semibold text-ink">{{ number_format($spinWinnings, 2, ',', '.') }} ₺</p>
                <p class="mt-1 text-xs text-ink-2">{{ $spinCredits }} hak</p>
            </div>
            <a href="{{ route('account.affiliate') }}" class="rounded-[20px] border border-ink/10 bg-paper p-5 transition hover:border-accent-300 hover:bg-white">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Satış Ortaklığı</p>
                @if ($affiliateCommissionRate === null)
                    <p class="mt-2 text-sm font-semibold text-ink">Oran bekleniyor</p>
                    <p class="mt-1 text-xs text-accent-600">Linkinizi paylaşın →</p>
                @else
                    <p class="mt-2 font-display text-xl font-semibold text-ink">{{ number_format($affiliateCommissionTotal, 2, ',', '.') }} ₺</p>
                    <p class="mt-1 text-xs text-ink-2">Oran: {{ number_format((float) $affiliateCommissionRate, 2, ',', '.') }}%</p>
                @endif
            </a>
        </div>

        <form method="get" action="{{ route('sites.index') }}" class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Hızlı arama</label>
            <div class="flex gap-2">
                <input type="search" name="q" placeholder="Site veya domain ara…" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink placeholder:text-ink-3 focus:border-ink/30 focus:ring-0">
                <button type="submit" class="shrink-0 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]">
                    Ara
                </button>
            </div>
        </form>

        <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
            <div class="border-b border-ink/10 px-5 py-4">
                <h2 class="font-display text-base font-semibold text-ink">Son siparişler</h2>
            </div>
            @if ($recentOrders->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-ink-2">Henüz siparişiniz yok.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-ink/10 bg-paper">
                                <th class="px-5 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">#</th>
                                <th class="px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Site</th>
                                <th class="px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Durum</th>
                                <th class="px-4 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tutar</th>
                                <th class="px-5 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tarih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/5">
                            @foreach ($recentOrders as $order)
                                <tr class="transition hover:bg-paper">
                                    <td class="px-5 py-3 text-ink-2">{{ $order->id }}</td>
                                    <td class="px-4 py-3 font-semibold text-ink">{{ $order->site?->domain ?? '—' }}</td>
                                    <td class="px-4 py-3 text-ink-2">{{ $order->status?->getLabel() }}</td>
                                    <td class="px-4 py-3 text-end font-semibold text-ink">{{ number_format((float) $order->price, 2, ',', '.') }} ₺</td>
                                    <td class="px-5 py-3 text-end text-ink-3">{{ $order->created_at?->format('d.m.Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
