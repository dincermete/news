<?php

namespace App\Services;

use App\Enums\BillingProfileType;
use App\Models\BillingProfile;
use Illuminate\Http\Request;

class BillingProfileResolver
{
    /**
     * Resolve a billing profile from request data, or return null when the
     * user submitted no billing information at all (billing is optional).
     *
     * @param  array<string, mixed>  $data
     */
    public function resolveOptional(Request $request, array $data): ?BillingProfile
    {
        if (filled($data['billing_profile_id'] ?? null)) {
            return $this->findExisting($request, (int) $data['billing_profile_id']);
        }

        if (blank($data['billing_type'] ?? null) && blank($data['tax_id'] ?? null) && blank($data['address'] ?? null)) {
            return null;
        }

        return $this->createFromData($request, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function resolveRequired(Request $request, array $data): BillingProfile
    {
        if (filled($data['billing_profile_id'] ?? null)) {
            return $this->findExisting($request, (int) $data['billing_profile_id']);
        }

        return $this->createFromData($request, $data);
    }

    protected function findExisting(Request $request, int $billingProfileId): BillingProfile
    {
        return BillingProfile::query()
            ->whereKey($billingProfileId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function createFromData(Request $request, array $data): BillingProfile
    {
        $type = BillingProfileType::from($data['billing_type']);

        return BillingProfile::query()->create([
            'user_id' => $request->user()->id,
            'type' => $type,
            'tax_id' => $data['tax_id'],
            'company_name' => $type === BillingProfileType::Corporate ? ($data['company_name'] ?? null) : null,
            'address' => $data['address'],
            'tax_office' => $type === BillingProfileType::Corporate ? ($data['tax_office'] ?? null) : null,
        ]);
    }
}
