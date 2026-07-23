<?php

namespace Tests\Feature;

use App\Enums\CustomerStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\User;
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

        Livewire::test(EditCustomer::class, ['record' => $customer->getRouteKey()])
            ->callAction('toggleStatus')
            ->assertNotified();

        $this->assertTrue($customer->fresh()->isSuspended());
        $this->assertSame(CustomerStatus::Suspended, $customer->fresh()->status);

        Livewire::test(EditCustomer::class, ['record' => $customer->getRouteKey()])
            ->callAction('toggleStatus')
            ->assertNotified();

        $this->assertFalse($customer->fresh()->isSuspended());
        $this->assertSame(CustomerStatus::Active, $customer->fresh()->status);
    }
}
