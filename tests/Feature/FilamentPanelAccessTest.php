<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_admin_panel(): void
    {
        $customer = User::factory()->customer()->create();

        $this->assertFalse($customer->canAccessPanel(filament()->getPanel('admin')));

        $this->actingAs($customer)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->canAccessPanel(filament()->getPanel('admin')));

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_editor_can_access_admin_panel(): void
    {
        $editor = User::factory()->editor()->create();

        $this->assertTrue($editor->canAccessPanel(filament()->getPanel('admin')));

        $this->actingAs($editor)
            ->get('/admin')
            ->assertOk();
    }

    public function test_customer_storefront_session_does_not_grant_admin_access(): void
    {
        $customer = User::factory()->customer()->create([
            'email' => 'musteri@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->post(route('login.store'), [
            'email' => 'musteri@example.com',
            'password' => 'password',
        ])->assertRedirect(route('account.dashboard'));

        $this->get('/admin')->assertForbidden();
    }
}
