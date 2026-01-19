<?php

namespace Tests\Feature\Cart;

use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveFromCartTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = app(CartService::class);
    }

    public function test_user_can_remove_item_from_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cartItem = $this->cartService->add($user, $product, 2);

        $result = $this->cartService->remove($user, $cartItem);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
        $this->assertEquals(0, $user->cartItems()->count());
    }

    public function test_removing_one_item_does_not_affect_other_items(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 10]);

        $cartItem1 = $this->cartService->add($user, $product1, 2);
        $cartItem2 = $this->cartService->add($user, $product2, 3);

        $this->cartService->remove($user, $cartItem1);

        $this->assertEquals(1, $user->cartItems()->count());
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem1->id]);
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem2->id,
            'quantity' => 3,
        ]);
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

    public function test_removing_cart_item_does_not_affect_product_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cartItem = $this->cartService->add($user, $product, 5);

        $this->cartService->remove($user, $cartItem);

        // Stock should remain unchanged (items in cart don't reduce stock)
        $this->assertEquals(10, $product->fresh()->stock_quantity);
    }

    public function test_can_remove_all_items_from_cart(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 10]);
        $product3 = Product::factory()->create(['stock_quantity' => 10]);

        $cartItem1 = $this->cartService->add($user, $product1, 1);
        $cartItem2 = $this->cartService->add($user, $product2, 2);
        $cartItem3 = $this->cartService->add($user, $product3, 3);

        $this->assertEquals(3, $user->cartItems()->count());

        $this->cartService->remove($user, $cartItem1);
        $this->cartService->remove($user, $cartItem2);
        $this->cartService->remove($user, $cartItem3);

        $this->assertEquals(0, $user->cartItems()->count());
    }
}
