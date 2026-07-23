@extends('layouts.account')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('content')
    @php
        $prizePayload = $prizes->map(fn ($prize) => [
            'id' => $prize->id,
            'name' => $prize->name,
            'type' => $prize->type?->value,
            'value' => $prize->value !== null ? (float) $prize->value : null,
        ])->values();
        $palette = ['#2248ab', '#1c3c91', '#1b3377', '#3d5fbf', '#6a88d4', '#ff3738', '#ed1f20', '#c81415', '#9ab0e4', '#ff6b6c'];
        $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    @endphp

    <div
        class="space-y-5"
        x-data="spinWheel({
            prizes: {{ \Illuminate\Support\Js::from($prizePayload) }},
            credits: {{ (int) $spinCredits }},
            spinUrl: @js(route('account.spin-wheel.spin')),
            csrf: @js(csrf_token()),
            palette: {{ \Illuminate\Support\Js::from($palette) }},
        })"
    >
        <div>
            <p><span class="{{ $chip }}">Hesabım</span></p>
            <h1 class="mt-3 font-display text-2xl font-medium text-ink sm:text-[28px]">Çarkıfelek</h1>
            <p class="mt-1.5 text-sm text-ink-2">Haklarınızı kullanarak ödül kazanın.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Çarkıfelek Hakkı</p>
                <p class="mt-2 font-display text-2xl font-semibold text-ink" x-text="credits"></p>
            </div>
            <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Toplam Kazanç</p>
                <p class="mt-2 font-display text-2xl font-semibold text-ink">{{ number_format($totalWinnings, 2, ',', '.') }} ₺</p>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-3">
            <div class="rounded-[20px] border border-ink/10 bg-paper p-6 lg:col-span-2">
                <div class="relative mx-auto flex max-w-md flex-col items-center">
                    <div class="relative z-10 mb-[-10px] text-accent-600" aria-hidden="true">
                        <svg class="size-6" viewBox="0 0 24 24" fill="currentColor"><path d="M12 16 6 8h12z"/></svg>
                    </div>

                    <div class="relative size-72 sm:size-80">
                        <div
                            class="absolute inset-0 rounded-full border-4 border-white shadow-pop transition-transform ease-out"
                            :style="wheelStyle()"
                            :class="{ 'duration-[4000ms]': spinning, 'duration-0': !spinning }"
                        >
                            <template x-if="prizes.length === 0">
                                <div class="flex h-full items-center justify-center rounded-full bg-white text-sm text-ink-2">
                                    Aktif ödül yok
                                </div>
                            </template>
                            <template x-if="prizes.length > 0">
                                <div class="relative h-full w-full overflow-hidden rounded-full" :style="conicStyle()">
                                    <template x-for="(prize, index) in prizes" :key="prize.id">
                                        <span
                                            class="pointer-events-none absolute left-1/2 top-[12%] origin-bottom -translate-x-1/2 text-center text-[10px] font-semibold uppercase tracking-wide text-white drop-shadow"
                                            :style="labelStyle(index)"
                                            x-text="prizeLabel(prize)"
                                        ></span>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <button
                            type="button"
                            class="absolute left-1/2 top-1/2 z-20 flex size-20 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border-4 border-white bg-gradient-to-br from-accent-600 to-accent-800 text-sm font-bold text-white shadow-pop disabled:cursor-not-allowed disabled:from-ink/30 disabled:to-ink/30"
                            @click="spin()"
                            :disabled="spinning || credits <= 0 || prizes.length === 0"
                        >
                            ÇEVİR
                        </button>
                    </div>

                    <p class="mt-4 text-center text-sm text-ink-2" x-show="credits <= 0 && !result" x-cloak>
                        Bakiye yükleyerek hak kazanabilirsiniz.
                    </p>
                    <p class="mt-4 text-center text-sm font-semibold text-accent-700" x-show="result" x-text="result" x-cloak></p>
                    <p class="mt-2 text-center text-sm text-brand-600" x-show="error" x-text="error" x-cloak></p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                    <h2 class="font-display text-base font-semibold text-ink">Bakiye Yükledikçe Hak Kazan</h2>
                    <div class="mt-3 overflow-hidden rounded-xl border border-ink/10">
                        <table class="w-full border-collapse text-sm">
                            <thead>
                                <tr class="bg-white">
                                    <th class="px-3 py-2 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tutar</th>
                                    <th class="px-3 py-2 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Hak</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink/5 bg-white">
                                @forelse ($packages as $package)
                                    <tr>
                                        <td class="px-3 py-2.5 text-ink">{{ number_format((float) $package->amount, 2, ',', '.') }} ₺</td>
                                        <td class="px-3 py-2.5 font-semibold text-ink">{{ $package->spin_credits }} hak</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-3 py-4 text-center text-ink-2">Paket bulunamadı.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <a
                        href="{{ route('account.payment-notification') }}"
                        class="group mt-4 flex w-full items-center justify-center gap-x-2 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]"
                    >
                        Bakiye Yükle
                        <svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white">
            <div class="border-b border-ink/10 px-5 py-4">
                <h2 class="font-display text-base font-semibold text-ink">Geçmiş Kazançlarım</h2>
            </div>
            @if ($recentSpins->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-ink-2">Henüz çevirme yok.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[480px] border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-ink/10 bg-paper">
                                <th class="px-5 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Ödül</th>
                                <th class="px-4 py-3 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tarih</th>
                                <th class="px-5 py-3 text-end text-[11px] font-semibold uppercase tracking-wide text-ink-3">Durum</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/5">
                            @foreach ($recentSpins as $spin)
                                <tr class="transition hover:bg-paper">
                                    <td class="px-5 py-3.5 font-semibold text-ink">
                                        {{ $spin->prize?->name ?? '—' }}
                                        @if ($spin->prize?->type === \App\Enums\SpinPrizeType::Balance)
                                            <span class="font-normal text-ink-3">({{ number_format((float) $spin->prize->value, 2, ',', '.') }} ₺)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-ink-3">{{ $spin->created_at?->format('d.m.Y H:i') }}</td>
                                    <td class="px-5 py-3.5 text-end">
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                                            Kazanıldı
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <h2 class="font-display text-base font-semibold text-ink">Nasıl Çalışır?</h2>
            <ol class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['1', 'Bakiye yükle', 'Paket tutarına göre çark hakkı kazanırsın.'],
                    ['2', 'Çarkı çevir', 'Her çevirme 1 hak harcar.'],
                    ['3', 'Ödülü kazan', 'Bakiye ödülleri cüzdanına eklenir.'],
                    ['4', 'Tekrar dene', 'Hakların bitince yeniden yükleme yap.'],
                ] as [$no, $title, $text])
                    <li class="rounded-xl border border-ink/10 bg-white p-4">
                        <span class="inline-flex size-7 items-center justify-center rounded-lg bg-ink font-display text-xs font-bold text-white">{{ $no }}</span>
                        <p class="mt-2.5 text-sm font-semibold text-ink">{{ $title }}</p>
                        <p class="mt-1 text-xs text-ink-2">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('spinWheel', (config) => ({
            prizes: config.prizes || [],
            credits: config.credits || 0,
            spinUrl: config.spinUrl,
            csrf: config.csrf,
            palette: config.palette || [],
            rotation: 0,
            spinning: false,
            result: '',
            error: '',

            segmentAngle() {
                return this.prizes.length > 0 ? 360 / this.prizes.length : 360;
            },

            conicStyle() {
                if (this.prizes.length === 0) {
                    return 'background: #f8f9fa';
                }

                const angle = this.segmentAngle();
                const stops = this.prizes.map((_, index) => {
                    const color = this.palette[index % this.palette.length];
                    const start = index * angle;
                    const end = (index + 1) * angle;
                    return `${color} ${start}deg ${end}deg`;
                }).join(', ');

                return `background: conic-gradient(${stops});`;
            },

            wheelStyle() {
                return `transform: rotate(${this.rotation}deg);`;
            },

            labelStyle(index) {
                const angle = this.segmentAngle();
                const mid = index * angle + angle / 2;
                return `transform: rotate(${mid}deg); width: 4.5rem; transform-origin: center 140px;`;
            },

            prizeLabel(prize) {
                if (prize.type === 'balance' && prize.value != null) {
                    return `${Number(prize.value).toLocaleString('tr-TR', { maximumFractionDigits: 0 })}₺`;
                }
                return (prize.name || 'Boş').slice(0, 10);
            },

            async spin() {
                if (this.spinning || this.credits <= 0 || this.prizes.length === 0) {
                    return;
                }

                this.spinning = true;
                this.error = '';
                this.result = '';

                try {
                    const response = await fetch(this.spinUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({}),
                    });

                    const data = await response.json();

                    if (! response.ok) {
                        this.error = data.message || 'Çevirme başarısız.';
                        this.spinning = false;
                        return;
                    }

                    const angle = this.segmentAngle();
                    const targetCenter = data.segment_index * angle + angle / 2;
                    const current = ((this.rotation % 360) + 360) % 360;
                    const desired = (360 - targetCenter) % 360;
                    const delta = (desired - current + 360) % 360;
                    const turns = 5;

                    this.rotation = this.rotation + turns * 360 + delta;
                    this.credits = data.spin_credits;

                    setTimeout(() => {
                        this.result = `Kazandınız: ${data.prize.label}`;
                        this.spinning = false;
                    }, 4200);
                } catch (e) {
                    this.error = 'Bağlantı hatası. Lütfen tekrar deneyin.';
                    this.spinning = false;
                }
            },
        }));
    });
</script>
@endpush
