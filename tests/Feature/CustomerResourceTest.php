<?php

namespace Tests\Feature;

use App\Enums\CustomerStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_only_customers(): void
    {
        $admin = User::factory()->admin()->create();
        $customers = User::factory()->customer()->count(2)->create();
        User::factory()->editor()->create();

        $this->actingAs($admin);

        Livewire::test(ListCustomers::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($customers)
            ->assertCanNotSeeTableRecords([$admin]);
    }

    public function test_admin_can_create_customer_manually(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'name' => 'Yeni Müşteri',
                'email' => 'yeni@example.com',
                'phone' => '5551112233',
                'status' => CustomerStatus::Active->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $customer = User::query()->where('email', 'yeni@example.com')->first();

        $this->assertNotNull($customer);
        $this->assertSame(UserRole::Customer, $customer->role);
        $this->assertSame(CustomerStatus::Active, $customer->status);
        $this->assertTrue($customer->isCustomer());
        $this->assertFalse($customer->isSuspended());
    }

    public function test_admin_can_filter_customers_by_status(): void
    {
        $admin = User::factory()->admin()->create();
        $active = User::factory()->customer()->create(['name' => 'Aktif Müşteri']);
        $suspended = User::factory()->customer()->suspended()->create(['name' => 'Askıdaki Müşteri']);

        $this->actingAs($admin);

        Livewire::test(ListCustomers::class)
            ->filterTable('status', CustomerStatus::Suspended->value)
            ->assertCanSeeTableRecords([$suspended])
            ->assertCanNotSeeTableRecords([$active]);
    }

    public function test_admin_can_toggle_customer_status(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $this->actingAs($admin);

        Livewire::test(ViewCustomer::class, ['record' => $customer->getRouteKey()])
            ->callAction('toggleStatus')
            ->assertNotified();

        $this->assertTrue($customer->fresh()->isSuspended());
        $this->assertSame(CustomerStatus::Suspended, $customer->fresh()->status);

        Livewire::test(ViewCustomer::class, ['record' => $customer->getRouteKey()])
            ->callAction('toggleStatus')
            ->assertNotified();

        $this->assertFalse($customer->fresh()->isSuspended());
        $this->assertSame(CustomerStatus::Active, $customer->fresh()->status);
    }

    public function test_admin_can_edit_customer_via_modal(): void
    {
        $admin = User::factory()->admin()->create();
        $referrer = User::factory()->customer()->create();
        $customer = User::factory()->customer()->create([
            'name' => 'Eski Ad',
            'email' => 'eski@example.com',
            'phone' => '5550000000',
        ]);

        $this->actingAs($admin);

        Livewire::test(ViewCustomer::class, ['record' => $customer->getRouteKey()])
            ->callAction('edit', [
                'name' => 'Yeni Ad',
                'email' => 'yeni-ad@example.com',
                'phone' => '5559998877',
                'status' => CustomerStatus::Suspended->value,
                'email_consent' => true,
                'sms_consent' => true,
                'affiliate_code' => 'MODALCODE',
                'affiliate_commission_rate' => 12.5,
                'referred_by_id' => $referrer->id,
            ])
            ->assertHasNoActionErrors()
            ->assertNotified();

        $customer->refresh();

        $this->assertSame('Yeni Ad', $customer->name);
        $this->assertSame('yeni-ad@example.com', $customer->email);
        $this->assertSame('5559998877', $customer->phone);
        $this->assertSame(CustomerStatus::Suspended, $customer->status);
        $this->assertTrue($customer->email_consent);
        $this->assertTrue($customer->sms_consent);
        $this->assertSame('MODALCODE', $customer->affiliate_code);
        $this->assertSame('12.50', (string) $customer->affiliate_commission_rate);
        $this->assertSame($referrer->id, $customer->referred_by_id);

        $this->get('/admin/customers/'.$customer->getRouteKey().'/edit')->assertNotFound();
    }

    public function test_admin_can_edit_customer_from_table_modal(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create(['name' => 'Tablo Müşteri']);

        $this->actingAs($admin);

        Livewire::test(ListCustomers::class)
            ->callAction(TestAction::make('edit')->table($customer), [
                'name' => 'Tablo Güncel',
                'email' => $customer->email,
                'status' => CustomerStatus::Active->value,
            ])
            ->assertHasNoActionErrors();

        $this->assertSame('Tablo Güncel', $customer->fresh()->name);
    }
}
