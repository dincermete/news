@php
    use App\Enums\BillingProfileType;

    /** @var \Illuminate\Support\Collection<int, \App\Models\BillingProfile> $billingProfiles */
    $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0';
@endphp
<div
    class="rounded-[20px] border border-ink/10 bg-paper p-5"
    x-data="{
        billingMode: '{{ old('billing_profile_id') ? 'existing' : ($billingProfiles->isNotEmpty() ? 'existing' : 'new') }}',
        billingType: '{{ old('billing_type', BillingProfileType::Individual->value) }}',
    }"
>
    <h2 class="font-display text-base font-semibold text-ink">Fatura Bilgileri</h2>
    <p class="mt-1 text-xs text-ink-3">Dijital ürün satışında adres bilgisi gerekmez.</p>

    <div class="mt-4 space-y-3">
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
                <label class="{{ $label }}">Kayıtlı fatura profili</label>
                <select name="billing_profile_id" class="{{ $input }}" :disabled="billingMode !== 'existing'">
                    @foreach ($billingProfiles as $profile)
                        <option value="{{ $profile->id }}" @selected((int) old('billing_profile_id', $billingProfiles->first()->id) === (int) $profile->id)>
                            {{ $profile->displayName() }} — {{ $profile->type?->getLabel() }} ({{ $profile->tax_id }})
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="space-y-3" x-show="billingMode === 'new' || {{ $billingProfiles->isEmpty() ? 'true' : 'false' }}" x-cloak>
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="{{ $label }}">Fatura tipi</label>
                    <select
                        name="billing_type"
                        class="{{ $input }}"
                        x-model="billingType"
                        :disabled="billingMode !== 'new' && {{ $billingProfiles->isNotEmpty() ? 'true' : 'false' }}"
                    >
                        @foreach (BillingProfileType::cases() as $type)
                            <option value="{{ $type->value }}">{{ $type->getLabel() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" x-text="billingType === 'corporate' ? 'VKN' : 'TCKN'"></label>
                    <input
                        type="text"
                        name="tax_id"
                        value="{{ old('tax_id') }}"
                        class="{{ $input }}"
                        :disabled="billingMode !== 'new' && {{ $billingProfiles->isNotEmpty() ? 'true' : 'false' }}"
                        required
                    >
                </div>
            </div>

            <div x-show="billingType === 'corporate'" x-cloak class="space-y-3">
                <div>
                    <label class="{{ $label }}">Ünvan / şirket</label>
                    <input
                        type="text"
                        name="company_name"
                        value="{{ old('company_name') }}"
                        class="{{ $input }}"
                        :disabled="billingMode !== 'new' && {{ $billingProfiles->isNotEmpty() ? 'true' : 'false' }}"
                    >
                </div>
                <div>
                    <label class="{{ $label }}">Vergi dairesi</label>
                    <input
                        type="text"
                        name="tax_office"
                        value="{{ old('tax_office') }}"
                        class="{{ $input }}"
                        :disabled="billingMode !== 'new' && {{ $billingProfiles->isNotEmpty() ? 'true' : 'false' }}"
                    >
                </div>
            </div>
        </div>
    </div>
</div>
