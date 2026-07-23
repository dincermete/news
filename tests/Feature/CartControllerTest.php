<?php

namespace Tests\Feature;

use App\Enums\CartStatus;
use App\Enums\ContentMode;
use App\Enums\Currency;
use App\Enums\ProductType;
use App\Enums\SiteStatus;
use App\Models\ArticleWordPackage;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Site;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_and_view_cart_item(): void
    {
        $site = Site::factory()->create([
            'status' => SiteStatus::Active,
            'price' => 150,
            'discount_price' => null,
            'currency' => Currency::Try,
        ]);

        $this->post(route('cart.add'), ['site_id' => $site->id])
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseHas(CartItem::class, [
            'site_id' => $site->id,
            'product_type' => ProductType::SiteArticle->value,
            'price' => 150,
        ]);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee($site->domain)
            ->assertSee('150,00');
    }

    public function test_authenticated_user_cart_uses_user_id(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create([
            'status' => SiteStatus::Active,
            'price' => 80,
            'discount_price' => null,
        ]);

        $this->actingAs($user)
            ->post(route('cart.add'), ['site_id' => $site->id])
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseHas(Cart::class, [
            'user_id' => $user->id,
            'status' => CartStatus::Active->value,
        ]);
    }

    public function test_remove_item_deletes_cart_item(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);
        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'site_id' => Site::factory()->create(['status' => SiteStatus::Active]),
        ]);

        $this->actingAs($user)
            ->delete(route('cart.remove', $item))
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseMissing(CartItem::class, ['id' => $item->id]);
    }

    public function test_update_content_file_upload_mode(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'content_mode' => ContentMode::FileUpload,
            'content_payload' => null,
            'site_id' => Site::factory()->create(['status' => SiteStatus::Active, 'price' => 100]),
            'price' => 100,
        ]);

        $file = UploadedFile::fake()->create('makale.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $this->actingAs($user)
            ->patch(route('cart.update-content', $item), [
                'content_mode' => ContentMode::FileUpload->value,
                'target_url' => 'https://example.com/hedef',
                'file' => $file,
            ])
            ->assertRedirect(route('cart.index'));

        $item->refresh();

        $this->assertSame(ContentMode::FileUpload, $item->content_mode);
        $this->assertSame('https://example.com/hedef', $item->content_payload['target_url'] ?? null);
        $this->assertNotEmpty($item->content_payload['file_path'] ?? null);
        Storage::disk('local')->assertExists($item->content_payload['file_path']);
    }

    public function test_update_content_ai_article_mode_with_package(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $site = Site::factory()->create([
            'status' => SiteStatus::Active,
            'price' => 100,
            'discount_price' => null,
        ]);
        $package = ArticleWordPackage::factory()->create([
            'word_count' => 500,
            'price' => 25,
            'is_active' => true,
        ]);
        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'site_id' => $site->id,
            'price' => 100,
            'content_mode' => ContentMode::FileUpload,
        ]);

        $this->actingAs($user)
            ->patch(route('cart.update-content', $item), [
                'content_mode' => ContentMode::AiArticle->value,
                'article_word_package_id' => $package->id,
                'keywords' => 'seo, backlink',
                'brief' => 'Kısa brief',
                'target_url' => 'https://example.com',
            ])
            ->assertRedirect(route('cart.index'));

        $item->refresh();

        $this->assertSame(ContentMode::AiArticle, $item->content_mode);
        $this->assertSame($package->id, $item->article_word_package_id);
        $this->assertSame('seo, backlink', $item->content_payload['keywords'] ?? null);
        $this->assertSame('125.00', $item->price);
    }

    public function test_apply_coupon_preview_stores_session_without_redemption(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'price' => 200,
            'site_id' => Site::factory()->create(['status' => SiteStatus::Active]),
        ]);

        Coupon::factory()->percentage(10)->create([
            'code' => 'SAVE10',
            'min_cart_amount' => 100,
        ]);

        $this->actingAs($user)
            ->post(route('cart.apply-coupon'), ['coupon_code' => 'SAVE10'])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status');

        $this->assertSame('SAVE10', session(CartService::SESSION_COUPON_KEY));
        $this->assertDatabaseCount('coupon_redemptions', 0);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('SAVE10');
    }

    public function test_apply_invalid_coupon_returns_error(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'price' => 50,
            'site_id' => Site::factory()->create(['status' => SiteStatus::Active]),
        ]);

        $this->actingAs($user)
            ->from(route('cart.index'))
            ->post(route('cart.apply-coupon'), ['coupon_code' => 'YOK'])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHasErrors('coupon_code');
    }

    public function test_cannot_remove_another_users_cart_item(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $owner->id]);
        $item = CartItem::factory()->create(['cart_id' => $cart->id]);

        $this->actingAs($attacker)
            ->delete(route('cart.remove', $item))
            ->assertForbidden();

        $this->assertDatabaseHas(CartItem::class, ['id' => $item->id]);
    }
}
