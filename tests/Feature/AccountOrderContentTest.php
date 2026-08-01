<?php

namespace Tests\Feature;

use App\Enums\ContentMode;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountOrderContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_configure_unconfigured_order_after_checkout(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $group = OrderGroup::factory()->create(['user_id' => $user->id]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_group_id' => $group->id,
            'status' => OrderStatus::ContentPending,
            'product_type' => ProductType::SiteArticle,
            'content_mode' => null,
            'content_payload' => null,
            'site_id' => Site::factory(),
        ]);

        $file = UploadedFile::fake()->create('makale.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $this->actingAs($user)
            ->patch(route('account.orders.content.update', [$group, $order]), [
                'content_mode' => ContentMode::FileUpload->value,
                'file' => $file,
                'note' => 'Sonradan yapılandırıldı',
            ])
            ->assertRedirect(route('account.orders.show', $group))
            ->assertSessionHas('status');

        $order->refresh();

        $this->assertSame(ContentMode::FileUpload, $order->content_mode);
        $this->assertSame('Sonradan yapılandırıldı', $order->content_payload['note'] ?? null);
        $this->assertNotEmpty($order->content_payload['file_path'] ?? null);
        $this->assertTrue($order->isContentConfigured());
    }

    public function test_other_user_cannot_configure_order_content(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $group = OrderGroup::factory()->create(['user_id' => $owner->id]);
        $order = Order::factory()->create([
            'user_id' => $owner->id,
            'order_group_id' => $group->id,
            'status' => OrderStatus::ContentPending,
        ]);

        $this->actingAs($attacker)
            ->patch(route('account.orders.content.update', [$group, $order]), [
                'content_mode' => ContentMode::FileUpload->value,
                'target_url' => 'https://example.com',
            ])
            ->assertForbidden();
    }

    public function test_published_order_content_cannot_be_edited(): void
    {
        $user = User::factory()->create();
        $group = OrderGroup::factory()->create(['user_id' => $user->id]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_group_id' => $group->id,
            'status' => OrderStatus::Published,
            'product_type' => ProductType::SiteArticle,
            'content_mode' => ContentMode::FileUpload,
            'content_payload' => ['target_url' => 'https://example.com'],
        ]);

        $this->actingAs($user)
            ->from(route('account.orders.show', $group))
            ->patch(route('account.orders.content.update', [$group, $order]), [
                'content_mode' => ContentMode::FileUpload->value,
                'target_url' => 'https://changed.example.com',
            ])
            ->assertRedirect(route('account.orders.show', $group))
            ->assertSessionHasErrors('content');
    }
}
