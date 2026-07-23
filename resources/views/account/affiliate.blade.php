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
            <h1 class="mt-3 font-display text-2xl font-medium text-ink sm:text-[28px]">Satış Ortaklığı</h1>
            <p class="mt-1.5 text-sm text-ink-2">Arkadaşlarınızı davet edin, siparişlerinden komisyon kazanın.</p>
        </div>

        @if ($commissionRate === null)
            <div class="rounded-[20px] border border-amber-200 bg-amber-50 px-5 py-3.5 text-sm text-amber-900">
                Komisyon oranınız henüz tanımlanmamış. Oran atandıktan sonra referans siparişlerinden kazanç elde edebilirsiniz.
            </div>
        @else
            <div class="rounded-[20px] border border-accent-200 bg-accent-50 px-5 py-3.5 text-sm text-accent-900">
                Komisyon oranınız: <span class="font-bold">{{ number_format((float) $commissionRate, 2, ',', '.') }}%</span>
            </div>
        @endif

        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <h2 class="font-display text-base font-semibold text-ink">Yönlendirici linkiniz</h2>
            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                <input
                    type="text"
                    readonly
                    value="{{ $referralUrl }}"
                    class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink-2"
                    id="affiliate-referral-url"
                >
                <button
                    type="button"
                    class="inline-flex shrink-0 items-center justify-center rounded-xl border border-ink/10 bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:border-ink/25"
                    onclick="navigator.clipboard.writeText(document.getElementById('affiliate-referral-url').value)"
                >
                    Kopyala
                </button>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <a
                    href="{{ $whatsappShareUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]"
                >
                    WhatsApp ile paylaş
                </a>
                <a
                    href="{{ $emailShareUrl }}"
                    class="inline-flex items-center justify-center rounded-xl border border-ink/10 bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:border-ink/25"
                >
                    E-posta ile paylaş
                </a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Referansla Kayıt Olan</p>
                <p class="mt-2 font-display text-2xl font-semibold text-ink">{{ $referralsTotal }}</p>
                <p class="mt-1 text-xs text-ink-2">Bu ay: {{ $referralsThisMonth }}</p>
            </div>
            <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Kazandığınız Komisyon</p>
                <p class="mt-2 font-display text-2xl font-semibold text-ink">{{ number_format($commissionTotal, 2, ',', '.') }} ₺</p>
                <p class="mt-1 text-xs text-ink-2">Bu ay: {{ number_format($commissionThisMonth, 2, ',', '.') }} ₺</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
            <div class="border-b border-ink/10 px-5 py-4">
                <h2 class="font-display text-base font-semibold text-ink">Komisyon Geçmişim</h2>
            </div>

            @if ($commissions->isEmpty())
                <div class="px-5 py-10 text-center">
                    <p class="text-sm text-ink-2">Henüz komisyon kaydı yok</p>
                    <a
                        href="{{ $whatsappShareUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-4 inline-flex items-center justify-center rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]"
                    >
                        Linki Paylaş
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-ink/10 bg-paper">
                                <th class="px-5 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tarih</th>
                                <th class="px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Referans</th>
                                <th class="px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Sipariş</th>
                                <th class="px-4 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tutar</th>
                                <th class="px-5 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Durum</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/5">
                            @foreach ($commissions as $commission)
                                <tr class="transition hover:bg-paper">
                                    <td class="whitespace-nowrap px-5 py-3.5 text-ink-2">
                                        {{ $commission->created_at?->format('d.m.Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3.5 text-ink-2">
                                        {{ $commission->referredUser?->name ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-ink-2">
                                        #{{ $commission->order_id }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-end font-semibold text-ink">
                                        {{ number_format((float) $commission->amount, 2, ',', '.') }} ₺
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-end">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $tone($commission->status?->getColor()) }}">
                                            {{ $commission->status?->getLabel() ?? $commission->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
