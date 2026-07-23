@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    use App\Enums\BillingProfileType;
    use App\Enums\PaymentMethod;

    $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
    $btnDark = 'group inline-flex w-full items-center justify-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 py-3 text-sm font-semibold text-white transition hover:scale-[1.01] active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100';
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto px-5 py-9 sm:px-8" data-reveal>
                <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80">
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Son adım</span>
                    Güvenli ödeme
                </p>
                <h1 class="mt-4 font-display text-3xl font-medium leading-tight sm:text-4xl">Ödeme</h1>
                <p class="mt-1.5 text-sm text-white/60">Fatura bilgisi ve ödeme yöntemini seçin.</p>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        @if ($errors->any())
            <div class="mb-6 rounded-[20px] border border-brand-200 bg-brand-50 px-5 py-3.5 text-sm text-brand-800" role="alert">
                <ul class="list-disc space-y-0.5 ps-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                @if (session('wallet_topup'))
                    <p class="mt-2">
                        <a href="{{ route('account.payment-notification') }}" class="font-semibold text-brand-900 underline underline-offset-2">Bakiye Yükle</a>
                    </p>
                @endif
            </div>
        @endif

        <form
            method="post"
            action="{{ route('checkout.process') }}"
            class="grid gap-6 lg:grid-cols-3"
            x-data="{
                method: '{{ old('payment_method', PaymentMethod::Card->value) }}',
                contracts: {{ old('contracts_accepted') ? 'true' : 'false' }},
                billingMode: '{{ old('billing_profile_id') ? 'existing' : ($billingProfiles->isNotEmpty() ? 'existing' : 'new') }}',
                payable: {{ Js::from($payable) }}
            }"
        >
            @csrf

            <div class="space-y-5 lg:col-span-2">
                <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                    <h2 class="font-display text-base font-semibold text-ink">Fatura profili</h2>

                    @if ($billingProfiles->isNotEmpty())
                        <div class="mt-3 flex gap-4 text-sm">
                            <label class="inline-flex items-center gap-x-2 text-ink-2">
                                <input type="radio" name="billing_mode_ui" value="existing" x-model="billingMode" class="border-ink/20 text-ink focus:ring-0">
                                Kayıtlı profil
                            </label>
                            <label class="inline-flex items-center gap-x-2 text-ink-2">
                                <input type="radio" name="billing_mode_ui" value="new" x-model="billingMode" class="border-ink/20 text-ink focus:ring-0">
                                Yeni profil
                            </label>
                        </div>

                        <div class="mt-3" x-show="billingMode === 'existing'" x-cloak>
                            <select name="billing_profile_id" class="{{ $input }}" :disabled="billingMode !== 'existing'">
                                @foreach ($billingProfiles as $profile)
                                    <option value="{{ $profile->id }}" @selected((int) old('billing_profile_id', $billingProfiles->first()->id) === (int) $profile->id)>
                                        {{ $profile->displayName() }} — {{ $profile->type?->getLabel() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="mt-4 space-y-3" x-show="billingMode === 'new' || {{ $billingProfiles->isEmpty() ? 'true' : 'false' }}" x-cloak>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="{{ $label }}">Tip</label>
                                <select name="billing_type" class="{{ $input }}">
                                    @foreach (BillingProfileType::cases() as $type)
                                        <option value="{{ $type->value }}" @selected(old('billing_type', BillingProfileType::Individual->value) === $type->value)>{{ $type->getLabel() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="{{ $label }}">TCKN / VKN</label>
                                <input type="text" name="tax_id" value="{{ old('tax_id') }}" class="{{ $input }}">
                            </div>
                        </div>
                        <div>
                            <label class="{{ $label }}">Ünvan / şirket</label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">Adres</label>
                            <textarea name="address" rows="2" class="{{ $input }}">{{ old('address') }}</textarea>
                        </div>
                        <div>
                            <label class="{{ $label }}">Vergi dairesi</label>
                            <input type="text" name="tax_office" value="{{ old('tax_office') }}" class="{{ $input }}">
                        </div>
                    </div>
                </div>

                <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                    <h2 class="font-display text-base font-semibold text-ink">Ödeme yöntemi</h2>
                    <div class="mt-3 space-y-2.5">
                        @foreach ([
                            ['method' => PaymentMethod::Card, 'label' => 'Kredi / banka kartı'],
                            ['method' => PaymentMethod::BankTransfer, 'label' => 'Havale / EFT (−%'.rtrim(rtrim(number_format($bankTransferDiscountPercent, 2, '.', ''), '0'), '.').')'],
                            ['method' => PaymentMethod::Balance, 'label' => 'Cüzdan bakiyesi'],
                        ] as $option)
                            @php $method = $option['method']; @endphp
                            <label
                                class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border px-4 py-3 transition"
                                :class="method === '{{ $method->value }}' ? 'border-ink bg-white' : 'border-ink/10 bg-white hover:border-ink/25'"
                            >
                                <span class="inline-flex items-center gap-x-2.5 text-sm font-medium text-ink">
                                    <input type="radio" name="payment_method" value="{{ $method->value }}" x-model="method" class="border-ink/20 text-ink focus:ring-0" @checked(old('payment_method', PaymentMethod::Card->value) === $method->value)>
                                    {{ $option['label'] }}
                                </span>
                                <span class="font-display text-sm font-bold text-ink" x-text="(payable['{{ $method->value }}'] ?? 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺'"></span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                    <label class="flex items-start gap-x-2.5 text-sm text-ink-2">
                        <input type="checkbox" name="contracts_accepted" value="1" class="mt-0.5 size-4 rounded border-ink/20 text-ink focus:ring-0" x-model="contracts">
                        <span>
                            <a href="{{ route('pages.show', 'mesafeli-satis-sozlesmesi') }}" target="_blank" class="font-semibold text-accent-700 hover:text-accent-800">Mesafeli satış sözleşmesi</a>
                            ve
                            <a href="{{ route('pages.show', 'on-bilgilendirme-formu') }}" target="_blank" class="font-semibold text-accent-700 hover:text-accent-800">ön bilgilendirme formu</a>
                            nu okudum, onaylıyorum.
                        </span>
                    </label>
                </div>
            </div>

            <aside class="lg:col-span-1">
                <div class="rounded-[20px] border border-ink/10 bg-paper p-5 lg:sticky lg:top-28">
                    <h2 class="font-display text-base font-semibold text-ink">Özet</h2>
                    <dl class="mt-4 space-y-2.5 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-2">Ara toplam</dt>
                            <dd class="font-semibold text-ink">{{ number_format($summary['subtotal'], 2, ',', '.') }} ₺</dd>
                        </div>
                        @if ($summary['tier_discount'] > 0)
                            <div class="flex justify-between gap-3">
                                <dt class="text-ink-2">Kademe</dt>
                                <dd class="font-semibold text-emerald-600">−{{ number_format($summary['tier_discount'], 2, ',', '.') }} ₺</dd>
                            </div>
                        @endif
                        @if ($summary['coupon_discount'] > 0)
                            <div class="flex justify-between gap-3">
                                <dt class="text-ink-2">Kupon</dt>
                                <dd class="font-semibold text-emerald-600">−{{ number_format($summary['coupon_discount'], 2, ',', '.') }} ₺</dd>
                            </div>
                        @endif
                        <div class="flex justify-between gap-3 border-t border-ink/10 pt-3">
                            <dt class="font-display text-base font-semibold text-ink">Ödenecek</dt>
                            <dd class="font-display text-base font-bold text-ink" x-text="(payable[method] ?? 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺'"></dd>
                        </div>
                    </dl>

                    <button type="submit" class="{{ $btnDark }} mt-5" :disabled="!contracts">
                        <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                            <svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </span>
                        Siparişi tamamla
                    </button>
                    <a href="{{ route('cart.index') }}" class="mt-3 block text-center text-xs font-medium text-ink-3 hover:text-ink">Sepete dön</a>
                </div>
            </aside>
        </form>
    </div>
@endsection
