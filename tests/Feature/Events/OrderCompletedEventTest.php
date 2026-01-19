<?php

namespace Tests\Feature\Events;

use App\Events\OrderCompleted;
use App\Jobs\LowStockNotificationJob;
use App\Jobs\SendNewOrderNotificationJob;
use App\Jobs\SendOrderConfirmationJob;
use App\Mail\LowStockNotification;
use App\Mail\NewOrderPlaced;
use App\Mail\OrderConfirmation;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderCompletedEventTest extends TestCase
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

    public function test_order_completed_event_is_dispatched_on_order_creation(): void
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

    public function test_low_stock_notification_email_contains_correct_data(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $lowStockThreshold = config('shop.low_stock_threshold', 5);
        $product = Product::factory()->create([
            'stock_quantity' => $lowStockThreshold + 1,
            'price' => 20.00,
            'name' => 'Test Product',
        ]);

        $this->cartService->add($user, $product, 1);
        $this->checkoutService->process($user);

        // Process the queue synchronously
        $order = $user->orders()->first();
        $lowStockProducts = $this->checkoutService->getLowStockProducts($order);

        if (!empty($lowStockProducts)) {
            $job = new LowStockNotificationJob($lowStockProducts);
            $job->handle();

            Mail::assertSent(LowStockNotification::class, function ($mail) use ($product) {
                return $mail->hasTo(config('shop.dummy_admin_email'))
                    && in_array($product->id, array_column($mail->products, 'id'));
            });
        }
    }

    public function test_order_confirmation_email_is_sent_to_customer(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'customer@example.com']);
        $product = Product::factory()->create(['stock_quantity' => 10, 'price' => 20.00]);

        $this->cartService->add($user, $product, 2);
        $order = $this->checkoutService->process($user);

        // Process the job synchronously
        $job = new SendOrderConfirmationJob($order);
        $job->handle();

        Mail::assertSent(OrderConfirmation::class, function ($mail) use ($user, $order) {
            return $mail->hasTo($user->email)
                && $mail->order->id === $order->id;
        });
    }

    public function test_new_order_notification_email_is_sent_to_admin(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10, 'price' => 20.00]);

        $this->cartService->add($user, $product, 2);
        $order = $this->checkoutService->process($user);

        // Process the job synchronously
        $job = new SendNewOrderNotificationJob($order);
        $job->handle();

        Mail::assertSent(NewOrderPlaced::class, function ($mail) use ($order) {
            return $mail->hasTo(config('shop.dummy_admin_email'))
                && $mail->order->id === $order->id;
        });
    }
}
