@extends('layouts.account')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('content')
    @php
        $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
        $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
        $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
        $btnDark = 'group inline-flex w-full items-center justify-center gap-x-2 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]';
    @endphp

    <div class="space-y-5">
        <div>
            <p><span class="{{ $chip }}">Hesabım</span></p>
            <h1 class="mt-3 font-display text-2xl font-medium text-ink sm:text-[28px]">Bakiyenizi Yükleyin</h1>
            <p class="mt-1.5 text-sm text-ink-2">Bakiyenizi ne kadar yüklerseniz o kadar çok çark hakkı kazanırsınız. Kazandığınız haklarla anında ekstra bakiye çıkma şansı.</p>
        </div>

        @if ($errors->any())
            <div class="rounded-[20px] border border-brand-200 bg-brand-50 px-5 py-3.5 text-sm text-brand-800" role="alert">
                <ul class="list-disc space-y-0.5 ps-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <h2 class="font-display text-base font-semibold text-ink">Dilediğin kadar yükle</h2>
            <p class="mt-1 text-sm text-ink-2">İstediğin tutarı gir, her 100₺ için +3 çark hakkı kazan. Bakiyen ve çark hakkın ödeme sonrası anında tanımlanır.</p>
            <form method="post" action="{{ route('cart.add') }}" class="mt-4 flex flex-wrap items-end gap-3">
                @csrf
                <input type="hidden" name="product_type" value="balance">
                <div class="max-w-[220px] flex-1">
                    <label class="{{ $label }}">Tutar (₺)</label>
                    <input type="number" name="custom_topup_amount" min="{{ (int) $minAmount }}" step="1" placeholder="Örn. 3.000" required class="{{ $input }}">
                </div>
                @guest
                    <button type="button" class="rounded-2xl bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">
                        Sepete Ekle
                    </button>
                @else
                    <button type="submit" class="rounded-2xl bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]">
                        Sepete Ekle
                    </button>
                @endguest
            </form>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($packages as $package)
                <div class="rounded-[20px] border border-ink/10 bg-white p-5">
                    <p class="font-display text-xl font-semibold text-ink">{{ number_format((float) $package->amount, 0, ',', '.') }} ₺</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-accent-600">{{ $package->spin_credits }} çark hakkı</p>
                    <form method="post" action="{{ route('cart.add') }}" class="mt-4">
                        @csrf
                        <input type="hidden" name="product_type" value="balance">
                        <input type="hidden" name="wallet_topup_package_id" value="{{ $package->id }}">
                        @guest
                            <button type="button" class="{{ $btnDark }}" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">
                                Bakiye Yükle
                            </button>
                        @else
                            <button type="submit" class="{{ $btnDark }}">
                                Bakiye Yükle
                            </button>
                        @endguest
                    </form>
                </div>
            @empty
                <p class="text-sm text-ink-2">Şu anda tanımlı bakiye paketi yok.</p>
            @endforelse
        </div>

        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-display text-base font-semibold text-ink">Zaten bekleyen bir havale ödemeniz mi var?</h2>
                <a href="{{ route('account.payment-notification') }}" class="text-sm font-semibold text-accent-700 hover:text-accent-800">Ödeme bildirimi yapın →</a>
            </div>
        </div>

        <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
            <h2 class="font-display text-base font-semibold text-ink">Nasıl Çalışır?</h2>
            <ol class="mt-4 grid gap-3 sm:grid-cols-3">
                @foreach ([
                    ['1', 'Paket seç', "İhtiyacınıza uygun bakiye paketini seçin. 50₺'den 50.000₺'ye kadar farklı paketler arasından seçim yapabilirsiniz."],
                    ['2', 'Güvenle öde', 'Kredi kartı veya havale/EFT ile güvenli ödeme yapın. Tutar onaydan sonra hesabınıza geçer.'],
                    ['3', 'Çarkı çevir', 'Kazandığınız hakları kullanarak çarkı çevirin — büyük ödülleri yakalama şansı.'],
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
