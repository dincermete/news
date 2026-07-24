@extends('layouts.account')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('content')
    @php
        $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
        $tone = fn (?string $color) => match ($color) {
            'success' => 'bg-emerald-100 text-emerald-700',
            'warning' => 'bg-amber-100 text-amber-700',
            'danger' => 'bg-brand-100 text-brand-700',
            'info', 'primary' => 'bg-accent-100 text-accent-700',
            default => 'bg-ink/5 text-ink-3',
        };
    @endphp

    <div class="space-y-5">
        <div>
            <p><span class="{{ $chip }}">Hesabım</span></p>
            <h1 class="mt-3 font-display text-2xl font-medium text-ink sm:text-[28px]">Siparişlerim</h1>
        </div>

        <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
            @if ($orders->isEmpty())
                <p class="px-5 py-16 text-center text-sm text-ink-2">Henüz siparişiniz yok.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[620px] border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-ink/10 bg-paper">
                                <th class="px-5 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">#</th>
                                <th class="px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Site</th>
                                <th class="px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Durum</th>
                                <th class="px-4 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Fiyat</th>
                                <th class="px-5 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tarih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/5">
                            @foreach ($orders as $order)
                                <tr class="transition hover:bg-paper">
                                    <td class="px-5 py-3.5 text-ink-2">{{ $order->id }}</td>
                                    @php
                                        $orderLabel = $order->site?->domain ?? $order->instagramAccount?->handle ?? '—';
                                    @endphp
                                    <td class="px-4 py-3.5 font-semibold text-ink">
                                        @if ($order->order_group_id)
                                            <a href="{{ route('account.orders.show', $order->order_group_id) }}" class="hover:text-accent-700 hover:underline">{{ $orderLabel }}</a>
                                        @else
                                            {{ $orderLabel }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $tone($order->status?->getColor()) }}">
                                            {{ $order->status?->getLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-end font-semibold text-ink">{{ number_format((float) $order->price, 2, ',', '.') }} {{ $order->currency?->value ?? 'TRY' }}</td>
                                    <td class="px-5 py-3.5 text-end text-ink-3">{{ $order->created_at?->format('d.m.Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-ink/10 px-5 py-4">
                    {{ $orders->links('vendor.pagination.storefront') }}
                </div>
            @endif
        </div>
    </div>
@endsection
