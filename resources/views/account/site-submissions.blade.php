@extends('layouts.account')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('content')
    @php
        $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
        $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
        $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
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
            <h1 class="mt-3 font-display text-2xl font-medium text-ink sm:text-[28px]">Site Başvurularım</h1>
            <p class="mt-1.5 text-sm text-ink-2">Kataloğa eklemek istediğiniz siteleri bildirin.</p>
        </div>

        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <h2 class="font-display text-base font-semibold text-ink">Yeni başvuru</h2>
            <form method="post" action="{{ route('account.site-submissions.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <label class="{{ $label }}">Site URL</label>
                    <input type="url" name="url" value="{{ old('url') }}" required placeholder="https://ornek.com" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Fiyat (₺)</label>
                    <input type="number" name="price" value="{{ old('price') }}" required min="0" step="0.01" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Kategori</label>
                    <select name="site_category_id" class="{{ $input }}">
                        <option value="">Seçiniz (opsiyonel)</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('site_category_id') === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}">Site yaşı (yıl)</label>
                    <input type="number" name="age" value="{{ old('age') }}" min="0" max="100" class="{{ $input }}" placeholder="Opsiyonel">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 text-sm font-medium text-white transition hover:scale-[1.02] active:scale-[0.98]">
                        <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                            <svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </span>
                        Başvuru gönder
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
            <div class="border-b border-ink/10 px-5 py-4">
                <h2 class="font-display text-base font-semibold text-ink">Başvurularım</h2>
            </div>
            @if ($submissions->isEmpty())
                <p class="px-5 py-16 text-center text-sm text-ink-2">Henüz başvurunuz yok.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[680px] border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-ink/10 bg-paper">
                                <th class="px-5 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">URL</th>
                                <th class="px-4 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Fiyat</th>
                                <th class="px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Kategori</th>
                                <th class="px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Durum</th>
                                <th class="px-5 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tarih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/5">
                            @foreach ($submissions as $submission)
                                <tr class="transition hover:bg-paper">
                                    <td class="px-5 py-3.5">
                                        <a href="{{ $submission->url }}" target="_blank" rel="noopener" class="font-semibold text-accent-700 hover:text-accent-800">
                                            {{ $submission->url }}
                                        </a>
                                        @if ($submission->status === \App\Enums\SiteSubmissionStatus::Rejected && filled($submission->admin_note))
                                            <p class="mt-1 text-[11px] text-brand-600">{{ $submission->admin_note }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-end font-semibold text-ink">{{ number_format((float) $submission->price, 2, ',', '.') }} ₺</td>
                                    <td class="px-4 py-3.5 text-ink-2">{{ $submission->category?->name ?? '—' }}</td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $tone($submission->status?->getColor()) }}">
                                            {{ $submission->status?->getLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-end text-ink-3">{{ $submission->created_at?->format('d.m.Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-ink/10 px-5 py-4">
                    {{ $submissions->links('vendor.pagination.storefront') }}
                </div>
            @endif
        </div>
    </div>
@endsection
