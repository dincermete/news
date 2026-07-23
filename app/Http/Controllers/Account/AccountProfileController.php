<?php

namespace App\Http\Controllers\Account;

use App\Enums\BillingProfileType;
use App\Http\Controllers\Controller;
use App\Models\BillingProfile;
use App\Services\SeoMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountProfileController extends Controller
{
    public function edit(Request $request, SeoMetaService $seo): View
    {
        $user = $request->user();
        $billing = $user->billingProfiles()->latest('id')->first();

        return view('account.profile', [
            'meta' => $seo->forDefault(),
            'user' => $user,
            'billing' => $billing,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:32'],
            'billing_type' => ['required', Rule::enum(BillingProfileType::class)],
            'tax_id' => ['required', 'string', 'max:32'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:2000'],
            'tax_office' => ['nullable', 'string', 'max:255'],
            'email_consent' => ['sometimes', 'boolean'],
            'sms_consent' => ['sometimes', 'boolean'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $emailChanged = $user->email !== $data['email'];

        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'email_consent' => $request->boolean('email_consent'),
            'sms_consent' => $request->boolean('sms_consent'),
        ]);

        if (filled($data['password'] ?? null)) {
            $user->password = $data['password'];
        }

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        $type = BillingProfileType::from($data['billing_type']);

        $billingAttributes = [
            'type' => $type,
            'tax_id' => $data['tax_id'],
            'company_name' => $type === BillingProfileType::Corporate ? ($data['company_name'] ?? null) : null,
            'city' => $data['city'] ?? null,
            'district' => $data['district'] ?? null,
            'address' => $data['address'],
            'tax_office' => $type === BillingProfileType::Corporate ? ($data['tax_office'] ?? null) : null,
        ];

        $billing = $user->billingProfiles()->latest('id')->first();

        if ($billing instanceof BillingProfile) {
            $billing->update($billingAttributes);
        } else {
            $user->billingProfiles()->create($billingAttributes);
        }

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()
            ->route('account.profile')
            ->with('status', 'Profil güncellendi.');
    }
}
