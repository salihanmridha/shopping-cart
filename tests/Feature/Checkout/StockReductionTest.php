<?php

namespace Tests\Feature\Checkout;

use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockReductionTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cartService;
    private CheckoutService $checkoutService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = app(CartService::class);
        $this->checkoutService = app(CheckoutService::class);
    }

    public function test_product_stock_is_reduced_after_checkout(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->cartService->add($user, $product, 3);

        $this->checkoutService->process($user);

        $this->assertEquals(7, $product->fresh()->stock_quantity);
    }

    public function test_stock_is_reduced_for_all_products_in_order(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 20, 'price' => 10.00]);
        $product2 = Product::factory()->create(['stock_quantity' => 15, 'price' => 15.00]);
        $product3 = Product::factory()->create(['stock_quantity' => 8, 'price' => 20.00]);

        $this->cartService->add($user, $product1, 5);
        $this->cartService->add($user, $product2, 3);
        $this->cartService->add($user, $product3, 2);

        $this->checkoutService->process($user);

        $this->assertEquals(15, $product1->fresh()->stock_quantity); // 20 - 5
        $this->assertEquals(12, $product2->fresh()->stock_quantity); // 15 - 3
        $this->assertEquals(6, $product3->fresh()->stock_quantity);  // 8 - 2
    }

    public function test_stock_reduction_is_accurate_for_large_quantities(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 100]);

        $this->cartService->add($user, $product, 75);

        $this->checkoutService->process($user);

        $this->assertEquals(25, $product->fresh()->stock_quantity);
    }

    public function test_stock_can_be_reduced_to_zero(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 5, 'price' => 10.00]);

        $this->cartService->add($user, $product, 5);

        $this->checkoutService->process($user);

        $this->assertEquals(0, $product->fresh()->stock_quantity);
    }

    public function test_stock_is_not_reduced_if_checkout_fails(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10, 'price' => 10.00]);
        $product2 = Product::factory()->create(['stock_quantity' => 5, 'price' => 15.00]);

        $this->cartService->add($user, $product1, 2);
        $this->cartService->add($user, $product2, 3);

        // Reduce stock of second product after adding to cart
        $product2->update(['stock_quantity' => 1]);

        try {
            $this->checkoutService->process($user);
        } catch (\InvalidArgumentException $e) {
            // Expected exception
        }

        // Stock should remain unchanged
        $this->assertEquals(10, $product1->fresh()->stock_quantity);
        $this->assertEquals(1, $product2->fresh()->stock_quantity);
    }

    public function test_multiple_users_can_checkout_same_product(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 20, 'price' => 10.00]);

        $this->cartService->add($user1, $product, 5);
        $this->cartService->add($user2, $product, 3);

        $this->checkoutService->process($user1);
        $this->assertEquals(15, $product->fresh()->stock_quantity);

        $this->checkoutService->process($user2);
        $this->assertEquals(12, $product->fresh()->stock_quantity);
    }

    public function test_stock_reduction_is_persistent(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10, 'price' => 10.00]);

        $this->cartService->add($user, $product, 4);
        $this->checkoutService->process($user);

        // Verify in database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 6,
        ]);
    }
}
