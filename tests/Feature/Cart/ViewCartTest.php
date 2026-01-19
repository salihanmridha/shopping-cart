<?php

namespace Tests\Feature\Cart;

use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewCartTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = app(CartService::class);
    }

    public function test_user_can_view_their_cart_items(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10, 'name' => 'Product 1']);
        $product2 = Product::factory()->create(['stock_quantity' => 10, 'name' => 'Product 2']);

        $this->cartService->add($user, $product1, 2);
        $this->cartService->add($user, $product2, 3);

        $cartItems = $this->cartService->getCart($user);

        $this->assertCount(2, $cartItems);
        $this->assertTrue($cartItems->contains('product_id', $product1->id));
        $this->assertTrue($cartItems->contains('product_id', $product2->id));
    }

    public function test_cart_items_include_product_relationship(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'name' => 'Test Product',
            'price' => 99.99,
        ]);

        $this->cartService->add($user, $product, 2);

        $cartItems = $this->cartService->getCart($user);
        $cartItem = $cartItems->first();

        $this->assertTrue($cartItem->relationLoaded('product'));
        $this->assertEquals('Test Product', $cartItem->product->name);
        $this->assertEquals(99.99, $cartItem->product->price);
    }

    public function test_user_only_sees_their_own_cart_items(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->cartService->add($user1, $product, 2);
        $this->cartService->add($user2, $product, 3);

        $user1Cart = $this->cartService->getCart($user1);
        $user2Cart = $this->cartService->getCart($user2);

        $this->assertCount(1, $user1Cart);
        $this->assertCount(1, $user2Cart);
        $this->assertEquals(2, $user1Cart->first()->quantity);
        $this->assertEquals(3, $user2Cart->first()->quantity);
    }

    public function test_empty_cart_returns_empty_collection(): void
    {
        $user = User::factory()->create();

        $cartItems = $this->cartService->getCart($user);

        $this->assertCount(0, $cartItems);
        $this->assertTrue($cartItems->isEmpty());
    }

    public function test_cart_total_is_calculated_correctly(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10, 'price' => 10.00]);
        $product2 = Product::factory()->create(['stock_quantity' => 10, 'price' => 25.50]);
        $product3 = Product::factory()->create(['stock_quantity' => 10, 'price' => 5.75]);

        $this->cartService->add($user, $product1, 2);  // 2 * 10.00 = 20.00
        $this->cartService->add($user, $product2, 1);  // 1 * 25.50 = 25.50
        $this->cartService->add($user, $product3, 4);  // 4 * 5.75 = 23.00
        // Total = 68.50

        $total = $this->cartService->getCartTotal($user);

        $this->assertEquals(68.50, $total);
    }

    public function test_cart_total_for_empty_cart_is_zero(): void
    {
        $user = User::factory()->create();

        $total = $this->cartService->getCartTotal($user);

        $this->assertEquals(0.0, $total);
    }

    public function test_cart_total_with_single_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10, 'price' => 15.99]);

        $this->cartService->add($user, $product, 3);

        $total = $this->cartService->getCartTotal($user);

        $this->assertEquals(47.97, $total);
    }

    public function test_clearing_cart_removes_all_items(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 10]);

        $this->cartService->add($user, $product1, 2);
        $this->cartService->add($user, $product2, 3);

        $this->assertEquals(2, $user->cartItems()->count());

        $this->cartService->clear($user);

        $this->assertEquals(0, $user->cartItems()->count());
        $this->assertEquals(0.0, $this->cartService->getCartTotal($user));
    }

    public function test_clearing_cart_only_affects_specific_user(): void
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
}
