<?php

namespace Database\Factories;

use App\Models\BillingProfile;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = 'INV-'.now()->format('Y').'-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT);

        return [
            'order_id' => Order::factory(),
            'invoice_number' => $number,
            'pdf_path' => 'invoices/'.$number.'.pdf',
            'billing_profile_id' => null,
        ];
    }

    public function withBillingProfile(?BillingProfile $profile = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'billing_profile_id' => $profile?->id ?? BillingProfile::factory(),
        ]);
    }
}
