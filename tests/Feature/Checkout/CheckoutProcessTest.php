<?php

namespace Tests\Feature\Checkout;

use App\Events\OrderCompleted;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CheckoutProcessTest extends TestCase
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

    public function test_user_can_checkout_with_valid_cart(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10, 'price' => 50.00]);
        $product2 = Product::factory()->create(['stock_quantity' => 5, 'price' => 30.00]);

        $this->cartService->add($user, $product1, 2);
        $this->cartService->add($user, $product2, 1);

        $order = $this->checkoutService->process($user);

        $this->assertNotNull($order);
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals(130.00, $order->total_amount); // (2 * 50) + (1 * 30)
        $this->assertEquals('completed', $order->status);
    }

    public function test_order_items_are_created_from_cart_items(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10, 'price' => 25.00]);
        $product2 = Product::factory()->create(['stock_quantity' => 5, 'price' => 15.00]);

        $this->cartService->add($user, $product1, 3);
        $this->cartService->add($user, $product2, 2);

        $order = $this->checkoutService->process($user);

        $this->assertEquals(2, $order->orderItems()->count());

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 3,
            'price' => 25.00,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 2,
            'price' => 15.00,
        ]);
    }

    public function test_cart_is_cleared_after_successful_checkout(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10, 'price' => 20.00]);

        $this->cartService->add($user, $product, 2);
        $this->assertEquals(1, $user->cartItems()->count());

        $this->checkoutService->process($user);

        $this->assertEquals(0, $user->cartItems()->count());
    }

    public function test_cannot_checkout_with_empty_cart(): void
    {
        $user = User::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cart is empty');

        $this->checkoutService->process($user);
    }

    public function test_cannot_checkout_if_product_stock_becomes_insufficient(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 5, 'price' => 20.00]);

        $this->cartService->add($user, $product, 3);

        // Simulate stock reduction (e.g., another order was placed)
        $product->update(['stock_quantity' => 2]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->checkoutService->process($user);
    }

    public function test_checkout_validates_stock_for_all_items_before_processing(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10, 'price' => 20.00]);
        $product2 = Product::factory()->create(['stock_quantity' => 3, 'price' => 15.00]);

        $this->cartService->add($user, $product1, 2);
        $this->cartService->add($user, $product2, 2);

        // Reduce stock of second product
        $product2->update(['stock_quantity' => 1]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->checkoutService->process($user);

        // Verify no order was created
        $this->assertEquals(0, $user->orders()->count());

        // Verify cart was not cleared
        $this->assertEquals(2, $user->cartItems()->count());
    }

    public function test_checkout_is_atomic_transaction(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10, 'price' => 20.00]);
        $product2 = Product::factory()->create(['stock_quantity' => 5, 'price' => 15.00]);

        $this->cartService->add($user, $product1, 2);
        $this->cartService->add($user, $product2, 2);

        // Reduce stock of second product after adding to cart
        $product2->update(['stock_quantity' => 1]);

        try {
            $this->checkoutService->process($user);
        } catch (\InvalidArgumentException $e) {
            // Expected exception
        }

        // Verify nothing was committed
        $this->assertEquals(0, $user->orders()->count());
        $this->assertEquals(2, $user->cartItems()->count());
        $this->assertEquals(10, $product1->fresh()->stock_quantity); // Stock unchanged
        $this->assertEquals(1, $product2->fresh()->stock_quantity); // Stock unchanged
    }

    public function test_order_completed_event_is_fired(): void
    {
        Event::fake([OrderCompleted::class]);

        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10, 'price' => 20.00]);

        $this->cartService->add($user, $product, 2);

        $order = $this->checkoutService->process($user);

        Event::assertDispatched(OrderCompleted::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    }

    public function test_order_total_is_calculated_correctly(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10, 'price' => 12.50]);
        $product2 = Product::factory()->create(['stock_quantity' => 10, 'price' => 8.75]);
        $product3 = Product::factory()->create(['stock_quantity' => 10, 'price' => 15.00]);

        $this->cartService->add($user, $product1, 2);  // 2 * 12.50 = 25.00
        $this->cartService->add($user, $product2, 3);  // 3 * 8.75 = 26.25
        $this->cartService->add($user, $product3, 1);  // 1 * 15.00 = 15.00
        // Total = 66.25

        $order = $this->checkoutService->process($user);

        $this->assertEquals(66.25, $order->total_amount);
    }

    public function test_order_items_capture_price_at_checkout_time(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10, 'price' => 50.00]);

        $this->cartService->add($user, $product, 2);

        $order = $this->checkoutService->process($user);

        // Change product price after checkout
        $product->update(['price' => 75.00]);

        // Order item should still have original price
        $orderItem = $order->orderItems()->first();
        $this->assertEquals(50.00, $orderItem->price);
        $this->assertEquals(100.00, $order->total_amount); // 2 * 50.00
    }
}
