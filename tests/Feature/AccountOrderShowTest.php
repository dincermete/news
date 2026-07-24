<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\BillingProfile;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountOrderShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_order_detail(): void
    {
        $user = User::factory()->create();
        $group = OrderGroup::factory()->create(['user_id' => $user->id]);
        Order::factory()->create(['order_group_id' => $group->id, 'user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('account.orders.show', $group))
            ->assertOk()
            ->assertSee('Sipariş #'.$group->id)
            ->assertSee('Fatura bilgisi eksik');
    }

    public function test_other_user_cannot_view_order_detail(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $group = OrderGroup::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($attacker)
            ->get(route('account.orders.show', $group))
            ->assertForbidden();
    }

    public function test_saving_billing_info_attaches_profile_to_order_group(): void
    {
        $user = User::factory()->create();
        $group = OrderGroup::factory()->create(['user_id' => $user->id, 'billing_profile_id' => null]);

        $this->actingAs($user)
            ->post(route('account.orders.billing.store', $group), [
                'billing_type' => 'individual',
                'tax_id' => '12345678901',
                'address' => 'Test mahallesi no:1',
            ])
            ->assertRedirect(route('account.orders.show', $group));

        $group->refresh();

        $this->assertNotNull($group->billing_profile_id);
        $this->assertDatabaseHas(BillingProfile::class, [
            'id' => $group->billing_profile_id,
            'user_id' => $user->id,
            'tax_id' => '12345678901',
        ]);
    }

    public function test_saving_billing_info_regenerates_existing_invoice(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $group = OrderGroup::factory()->create(['user_id' => $user->id, 'billing_profile_id' => null]);
        Order::factory()->create(['order_group_id' => $group->id, 'user_id' => $user->id]);
        Payment::factory()->create([
            'order_id' => null,
            'order_group_id' => $group->id,
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::Paid,
            'currency' => Currency::Try,
        ]);

        $oldInvoice = Invoice::factory()->create([
            'order_id' => null,
            'order_group_id' => $group->id,
            'billing_profile_id' => null,
        ]);
        Storage::disk('local')->put($oldInvoice->pdf_path, 'old-pdf-contents');

        $this->actingAs($user)
            ->post(route('account.orders.billing.store', $group), [
                'billing_type' => 'individual',
                'tax_id' => '98765432109',
                'address' => 'Yeni adres',
            ])
            ->assertRedirect(route('account.orders.show', $group));

        $this->assertDatabaseMissing(Invoice::class, ['id' => $oldInvoice->id]);
        Storage::disk('local')->assertMissing($oldInvoice->pdf_path);

        $newInvoice = Invoice::query()->where('order_group_id', $group->id)->first();
        $this->assertNotNull($newInvoice);
        $this->assertNotSame($oldInvoice->invoice_number, $newInvoice->invoice_number);
        $this->assertSame($group->fresh()->billing_profile_id, $newInvoice->billing_profile_id);
    }
}
