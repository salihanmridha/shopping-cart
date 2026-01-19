<?php

namespace Tests\Feature\Cart;

use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddToCartTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = app(CartService::class);
    }

    public function test_authenticated_user_can_add_product_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $cartItem = $this->cartService->add($user, $product, 1);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->assertEquals($user->id, $cartItem->user_id);
        $this->assertEquals($product->id, $cartItem->product_id);
        $this->assertEquals(1, $cartItem->quantity);
    }

    public function test_adding_same_product_increments_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        // Add product first time
        $this->cartService->add($user, $product, 2);

        // Add same product again
        $cartItem = $this->cartService->add($user, $product, 3);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $this->assertEquals(5, $cartItem->quantity);

        // Ensure only one cart item exists for this product
        $this->assertEquals(1, $user->cartItems()->where('product_id', $product->id)->count());
    }

    public function test_cannot_add_product_with_zero_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 0]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->cartService->add($user, $product, 1);
    }

    public function test_cannot_add_quantity_exceeding_available_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->cartService->add($user, $product, 10);
    }

    public function test_cannot_add_quantity_exceeding_stock_when_product_already_in_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        // Add 7 items to cart
        $this->cartService->add($user, $product, 7);

        // Try to add 5 more (total would be 12, exceeding stock of 10)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->cartService->add($user, $product, 5);
    }

    public function test_cannot_add_negative_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be at least 1');

        $this->cartService->add($user, $product, -1);
    }

    public function test_cannot_add_zero_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be at least 1');

        $this->cartService->add($user, $product, 0);
    }

    public function test_cart_item_is_associated_with_correct_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->cartService->add($user1, $product, 1);

        // User 1 should have 1 cart item
        $this->assertEquals(1, $user1->cartItems()->count());

        // User 2 should have 0 cart items
        $this->assertEquals(0, $user2->cartItems()->count());
    }

    public function test_multiple_products_can_be_added_to_cart(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 5]);
        $product3 = Product::factory()->create(['stock_quantity' => 8]);

        $this->cartService->add($user, $product1, 2);
        $this->cartService->add($user, $product2, 1);
        $this->cartService->add($user, $product3, 3);

        $this->assertEquals(3, $user->cartItems()->count());

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product3->id,
            'quantity' => 3,
        ]);
    }
}
