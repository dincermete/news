<?php

namespace Tests\Feature;

use App\Enums\ContentSource;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Events\WalletRefundRequested;
use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $category = SiteCategory::factory()->create();
        $site = Site::factory()->create(['site_category_id' => $category->id]);
        $orders = Order::factory()->count(3)->create(['site_id' => $site->id]);

        $this->actingAs($admin);

        Livewire::test(ListOrders::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($orders);
    }

    public function test_admin_can_create_an_order_and_due_date_is_calculated(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $editor = User::factory()->editor()->create();
        $category = SiteCategory::factory()->create();
        $site = Site::factory()->create(['site_category_id' => $category->id]);

        $this->actingAs($admin);

        Livewire::test(CreateOrder::class)
            ->fillForm([
                'user_id' => $customer->id,
                'site_id' => $site->id,
                'content_source' => ContentSource::CustomerProvided->value,
                'price' => 150,
                'currency' => 'USD',
                'assigned_editor_id' => $editor->id,
                'status' => OrderStatus::PaymentPending->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $order = Order::query()->where('user_id', $customer->id)->first();

        $this->assertNotNull($order);
        $this->assertSame($editor->id, $order->assigned_editor_id);
        $this->assertTrue($order->due_date->equalTo(now()->addDays(2)->startOfDay()));
    }

    public function test_agency_written_sets_due_date_to_seven_days(): void
    {
        $order = Order::factory()->agencyWritten()->create([
            'due_date' => null,
        ]);

        $this->assertTrue($order->fresh()->due_date->equalTo(now()->addDays(7)->startOfDay()));
    }

    public function test_changing_content_source_recalculates_due_date(): void
    {
        $order = Order::factory()->customerProvided()->create();

        $this->assertTrue($order->due_date->equalTo(now()->addDays(2)->startOfDay()));

        $order->update(['content_source' => ContentSource::AgencyWritten]);

        $this->assertTrue($order->fresh()->due_date->equalTo(now()->addDays(7)->startOfDay()));
    }

    public function test_editor_assignment_only_allows_editor_role_users(): void
    {
        $admin = User::factory()->admin()->create();
        $editor = User::factory()->editor()->create();
        $nonEditor = User::factory()->admin()->create();
        $order = Order::factory()->create([
            'assigned_editor_id' => null,
        ]);

        $this->actingAs($admin);

        Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->fillForm([
                'assigned_editor_id' => $editor->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Order::class, [
            'id' => $order->id,
            'assigned_editor_id' => $editor->id,
        ]);

        $this->assertSame(UserRole::Editor, $editor->fresh()->role);
        $this->assertNotSame(UserRole::Editor, $nonEditor->fresh()->role);
    }

    public function test_approve_content_action_transitions_from_content_pending_to_review(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->status(OrderStatus::ContentPending)->create();

        $this->actingAs($admin);

        Livewire::test(ListOrders::class)
            ->callTableAction('approveContent', $order)
            ->assertNotified();

        $this->assertDatabaseHas(Order::class, [
            'id' => $order->id,
            'status' => OrderStatus::Review->value,
        ]);
    }

    public function test_queue_for_publish_action_transitions_from_review_to_in_queue(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->status(OrderStatus::Review)->create();

        $this->actingAs($admin);

        Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('queueForPublish')
            ->assertNotified();

        $this->assertDatabaseHas(Order::class, [
            'id' => $order->id,
            'status' => OrderStatus::InQueue->value,
        ]);
    }

    public function test_mark_published_action_transitions_from_in_queue_to_published(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->status(OrderStatus::InQueue)->create();

        $this->actingAs($admin);

        Livewire::test(ListOrders::class)
            ->callTableAction('markPublished', $order)
            ->assertNotified();

        $this->assertDatabaseHas(Order::class, [
            'id' => $order->id,
            'status' => OrderStatus::Published->value,
        ]);
    }

    public function test_invalid_status_transition_action_is_hidden(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->status(OrderStatus::PaymentPending)->create();

        $this->actingAs($admin);

        Livewire::test(ListOrders::class)
            ->assertTableActionHidden('approveContent', $order)
            ->assertTableActionHidden('markPublished', $order);
    }

    public function test_refund_action_sets_status_and_dispatches_event(): void
    {
        Event::fake([WalletRefundRequested::class]);

        $admin = User::factory()->admin()->create();
        $order = Order::factory()->status(OrderStatus::Review)->create();

        $this->actingAs($admin);

        Livewire::test(ListOrders::class)
            ->callTableAction('refund', $order)
            ->assertNotified();

        $this->assertDatabaseHas(Order::class, [
            'id' => $order->id,
            'status' => OrderStatus::Refunded->value,
        ]);

        Event::assertDispatched(WalletRefundRequested::class, function (WalletRefundRequested $event) use ($order): bool {
            return $event->order->is($order);
        });
    }
}
