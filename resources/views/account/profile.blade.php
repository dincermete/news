@extends('layouts.account')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('content')
    @php
        use App\Enums\BillingProfileType;
        $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
        $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
        $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
    @endphp

    <div class="space-y-5" x-data="{ type: '{{ old('billing_type', $billing?->type?->value ?? BillingProfileType::Individual->value) }}' }">
        <div>
            <p><span class="{{ $chip }}">Hesabım</span></p>
            <h1 class="mt-3 font-display text-2xl font-medium text-ink sm:text-[28px]">Profilim</h1>
            <p class="mt-1.5 text-sm text-ink-2">Kişisel ve fatura bilgilerinizi güncelleyin.</p>
        </div>

        <form method="post" action="{{ route('account.profile.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                <h2 class="font-display text-base font-semibold text-ink">Kişisel bilgiler</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Ad Soyad</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">E-posta</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Telefon</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="{{ $input }}">
                    </div>
                </div>
            </div>

            <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                <h2 class="font-display text-base font-semibold text-ink">Üyelik / fatura</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="{{ $label }}">Üyelik tipi</label>
                        <select name="billing_type" x-model="type" class="{{ $input }}">
                            @foreach (BillingProfileType::cases() as $case)
                                <option value="{{ $case->value }}">{{ $case->getLabel() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $label }}">
                            <span x-text="type === 'corporate' ? 'VKN' : 'TC Kimlik No'"></span>
                        </label>
                        <input type="text" name="tax_id" value="{{ old('tax_id', $billing?->tax_id) }}" class="{{ $input }}">
                    </div>
                    <div class="sm:col-span-2" x-show="type === 'corporate'" x-cloak>
                        <label class="{{ $label }}">Şirket ünvanı</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $billing?->company_name) }}" class="{{ $input }}">
                    </div>
                    <div x-show="type === 'corporate'" x-cloak>
                        <label class="{{ $label }}">Vergi dairesi</label>
                        <input type="text" name="tax_office" value="{{ old('tax_office', $billing?->tax_office) }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">İl</label>
                        <input type="text" name="city" value="{{ old('city', $billing?->city) }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">İlçe</label>
                        <input type="text" name="district" value="{{ old('district', $billing?->district) }}" class="{{ $input }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Adres</label>
                        <textarea name="address" rows="2" class="{{ $input }}">{{ old('address', $billing?->address) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                <h2 class="font-display text-base font-semibold text-ink">İzinler</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <label class="flex cursor-pointer items-center gap-x-2.5 text-ink-2">
                        <input type="checkbox" name="email_consent" value="1" class="size-4 rounded border-ink/20 text-ink focus:ring-0" @checked(old('email_consent', $user->email_consent))>
                        E-posta ile bilgilendirme almak istiyorum
                    </label>
                    <label class="flex cursor-pointer items-center gap-x-2.5 text-ink-2">
                        <input type="checkbox" name="sms_consent" value="1" class="size-4 rounded border-ink/20 text-ink focus:ring-0" @checked(old('sms_consent', $user->sms_consent))>
                        SMS ile bilgilendirme almak istiyorum
                    </label>
                </div>
            </div>

            <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                <h2 class="font-display text-base font-semibold text-ink">Şifre değiştir</h2>
                <p class="mt-1 text-xs text-ink-3">Boş bırakırsan şifren değişmez.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="{{ $label }}">Yeni şifre</label>
                        <input type="password" name="password" class="{{ $input }}" autocomplete="new-password">
                    </div>
                    <div>
                        <label class="{{ $label }}">Yeni şifre (tekrar)</label>
                        <input type="password" name="password_confirmation" class="{{ $input }}" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <button type="submit" class="group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 text-sm font-medium text-white transition hover:scale-[1.02] active:scale-[0.98]">
                <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                </span>
                Kaydet
            </button>
        </form>
    </div>
@endsection
