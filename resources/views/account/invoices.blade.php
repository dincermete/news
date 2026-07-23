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
            <p><span class="{{ $chip }}">Hesabım</span></p>
            <h1 class="mt-3 font-display text-2xl font-medium text-ink sm:text-[28px]">Faturalarım</h1>
        </div>

        <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
            @if ($invoices->isEmpty())
                <p class="px-5 py-16 text-center text-sm text-ink-2">Henüz faturanız yok.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-ink/10 bg-paper">
                                <th class="px-5 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Fatura No</th>
                                <th class="px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Sipariş</th>
                                <th class="px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tarih</th>
                                <th class="px-5 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/5">
                            @foreach ($invoices as $invoice)
                                <tr class="transition hover:bg-paper">
                                    <td class="px-5 py-3.5 font-semibold text-ink">{{ $invoice->invoice_number }}</td>
                                    <td class="px-4 py-3.5 text-ink-2">
                                        @if ($invoice->order_group_id)
                                            Grup #{{ $invoice->order_group_id }}
                                        @elseif ($invoice->order_id)
                                            #{{ $invoice->order_id }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-ink-3">{{ $invoice->created_at?->format('d.m.Y H:i') }}</td>
                                    <td class="px-5 py-3.5 text-end">
                                        @if ($invoice->pdf_path)
                                            <a href="{{ route('account.invoices.download', $invoice) }}" class="inline-flex items-center gap-x-1 text-sm font-semibold text-accent-700 hover:text-accent-800">
                                                PDF İndir
                                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-ink/10 px-5 py-4">
                    {{ $invoices->links('vendor.pagination.storefront') }}
                </div>
            @endif
        </div>
    </div>
@endsection
