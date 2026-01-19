<?php

namespace Tests\Feature\Security;

use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartIsolationTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = app(CartService::class);
    }

    public function test_user_cannot_see_another_users_cart(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->cartService->add($user1, $product, 3);
        $this->cartService->add($user2, $product, 5);

        $user1Cart = $this->cartService->getCart($user1);
        $user2Cart = $this->cartService->getCart($user2);

        $this->assertCount(1, $user1Cart);
        $this->assertCount(1, $user2Cart);
        $this->assertEquals(3, $user1Cart->first()->quantity);
        $this->assertEquals(5, $user2Cart->first()->quantity);
    }

    public function test_user_cannot_modify_another_users_cart_item(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $cartItem = $this->cartService->add($user1, $product, 2);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cart item does not belong to this user');

        $this->cartService->update($user2, $cartItem, 5);
    }

    public function test_user_cannot_remove_another_users_cart_item(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $cartItem = $this->cartService->add($user1, $product, 2);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cart item does not belong to this user');

        $this->cartService->remove($user2, $cartItem);
    }

    public function test_each_user_has_independent_cart(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 20]);
        $product2 = Product::factory()->create(['stock_quantity' => 20]);

        $this->cartService->add($user1, $product1, 2);
        $this->cartService->add($user1, $product2, 3);

        $this->cartService->add($user2, $product1, 5);

        $this->cartService->add($user3, $product2, 1);

        $this->assertEquals(2, $user1->cartItems()->count());
        $this->assertEquals(1, $user2->cartItems()->count());
        $this->assertEquals(1, $user3->cartItems()->count());
    }

    public function test_clearing_one_users_cart_does_not_affect_others(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->cartService->add($user1, $product, 2);
        $this->cartService->add($user2, $product, 3);

        $this->cartService->clear($user1);

        $this->assertEquals(0, $user1->cartItems()->count());
        $this->assertEquals(1, $user2->cartItems()->count());
    }

    public function test_user_can_only_view_their_own_orders(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $order1 = $user1->orders()->create(['total_amount' => 100.00, 'status' => 'completed']);
        $order2 = $user2->orders()->create(['total_amount' => 200.00, 'status' => 'completed']);

        $this->assertEquals(1, $user1->orders()->count());
        $this->assertEquals(1, $user2->orders()->count());
        $this->assertTrue($user1->orders()->where('id', $order1->id)->exists());
        $this->assertFalse($user1->orders()->where('id', $order2->id)->exists());
    }

    public function test_orders_are_correctly_associated_with_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 20, 'price' => 50.00]);

        $this->cartService->add($user1, $product, 2);
        $order1 = app(\App\Services\CheckoutService::class)->process($user1);

        $this->cartService->add($user2, $product, 3);
        $order2 = app(\App\Services\CheckoutService::class)->process($user2);

        $this->assertEquals($user1->id, $order1->user_id);
        $this->assertEquals($user2->id, $order2->user_id);
        $this->assertNotEquals($order1->id, $order2->id);
    }
}
