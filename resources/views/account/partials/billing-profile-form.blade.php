@php
    use App\Enums\BillingProfileType;

    $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
    $btnDark = 'group inline-flex items-center justify-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 py-3 text-sm font-semibold text-white transition hover:scale-[1.01] active:scale-[0.98]';
@endphp
<div
    class="rounded-[20px] border border-ink/10 bg-white p-5"
    x-data="{ billingMode: '{{ old('billing_profile_id') ? 'existing' : ($billingProfiles->isNotEmpty() ? 'existing' : 'new') }}' }"
>
    <h2 class="font-display text-base font-semibold text-ink">Fatura bilgilerini tamamla</h2>

    <form method="post" action="{{ route('account.orders.billing.store', $orderGroup) }}" class="mt-4 space-y-3">
        @csrf

        @if ($billingProfiles->isNotEmpty())
            <div class="flex gap-4 text-sm">
                <label class="inline-flex items-center gap-x-2 text-ink-2">
                    <input type="radio" name="billing_mode_ui" value="existing" x-model="billingMode" class="border-ink/20 text-ink focus:ring-0">
                    Kayıtlı profil
                </label>
                <label class="inline-flex items-center gap-x-2 text-ink-2">
                    <input type="radio" name="billing_mode_ui" value="new" x-model="billingMode" class="border-ink/20 text-ink focus:ring-0">
                    Yeni profil
                </label>
            </div>

            <div x-show="billingMode === 'existing'" x-cloak>
                <select name="billing_profile_id" class="{{ $input }}" :disabled="billingMode !== 'existing'">
                    @foreach ($billingProfiles as $profile)
                        <option value="{{ $profile->id }}" @selected((int) old('billing_profile_id', $billingProfiles->first()->id) === (int) $profile->id)>
                            {{ $profile->displayName() }} — {{ $profile->type?->getLabel() }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="space-y-3" x-show="billingMode === 'new' || {{ $billingProfiles->isEmpty() ? 'true' : 'false' }}" x-cloak>
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="{{ $label }}">Tip</label>
                    <select name="billing_type" class="{{ $input }}" :disabled="billingMode !== 'new'">
                        @foreach (BillingProfileType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('billing_type', BillingProfileType::Individual->value) === $type->value)>{{ $type->getLabel() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}">TCKN / VKN</label>
                    <input type="text" name="tax_id" value="{{ old('tax_id') }}" class="{{ $input }}" :disabled="billingMode !== 'new'">
                </div>
            </div>
            <div>
                <label class="{{ $label }}">Ünvan / şirket</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" class="{{ $input }}" :disabled="billingMode !== 'new'">
            </div>
            <div>
                <label class="{{ $label }}">Adres</label>
                <textarea name="address" rows="2" class="{{ $input }}" :disabled="billingMode !== 'new'">{{ old('address') }}</textarea>
            </div>
            <div>
                <label class="{{ $label }}">Vergi dairesi</label>
                <input type="text" name="tax_office" value="{{ old('tax_office') }}" class="{{ $input }}" :disabled="billingMode !== 'new'">
            </div>
        </div>

        <div class="flex items-center gap-3 pt-1">
            <button type="submit" class="{{ $btnDark }}">
                <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/15 text-white">
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                </span>
                Kaydet
            </button>
            @if ($orderGroup->billingProfile)
                <button type="button" class="text-sm font-medium text-ink-3 hover:text-ink" @click="editingBilling = false">Vazgeç</button>
            @endif
        </div>
    </form>
</div>
