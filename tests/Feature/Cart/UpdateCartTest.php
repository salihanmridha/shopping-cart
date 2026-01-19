<?php

namespace Tests\Feature\Cart;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateCartTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = app(CartService::class);
    }

    public function test_user_can_update_cart_item_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cartItem = $this->cartService->add($user, $product, 2);

        $updatedItem = $this->cartService->update($user, $cartItem, 5);

        $this->assertEquals(5, $updatedItem->quantity);
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5,
        ]);
    }

    public function test_can_decrease_cart_item_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cartItem = $this->cartService->add($user, $product, 5);

        $updatedItem = $this->cartService->update($user, $cartItem, 2);

        $this->assertEquals(2, $updatedItem->quantity);
    }

    public function test_cannot_update_quantity_below_one(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cartItem = $this->cartService->add($user, $product, 3);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be at least 1');

        $this->cartService->update($user, $cartItem, 0);
    }

    public function test_cannot_update_quantity_to_negative(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cartItem = $this->cartService->add($user, $product, 3);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be at least 1');

        $this->cartService->update($user, $cartItem, -5);
    }

    public function test_cannot_update_quantity_exceeding_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cartItem = $this->cartService->add($user, $product, 2);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->cartService->update($user, $cartItem, 15);
    }

    public function test_user_cannot_update_another_users_cart_item(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $cartItem = $this->cartService->add($user1, $product, 2);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cart item does not belong to this user');

        $this->cartService->update($user2, $cartItem, 5);
    }

    public function test_updating_cart_item_validates_current_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cartItem = $this->cartService->add($user, $product, 2);

        // Simulate stock reduction (e.g., another order was placed)
        $product->update(['stock_quantity' => 3]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->cartService->update($user, $cartItem, 5);
    }

    public function test_can_update_to_maximum_available_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cartItem = $this->cartService->add($user, $product, 2);

        $updatedItem = $this->cartService->update($user, $cartItem, 10);

        $this->assertEquals(10, $updatedItem->quantity);
    }

    public function test_updating_cart_item_returns_fresh_instance(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cartItem = $this->cartService->add($user, $product, 2);

        $updatedItem = $this->cartService->update($user, $cartItem, 7);

        // Updated instance should have new quantity
        $this->assertEquals(7, $updatedItem->quantity);

        // Fresh from database should match updated
        $this->assertEquals(7, $cartItem->fresh()->quantity);
    }

}
