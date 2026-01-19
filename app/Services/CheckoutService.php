<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    /**
     * @param CartService $cartService
     * @param StockService $stockService
     */
    public function __construct(
        private CartService $cartService,
        private StockService $stockService
    ) {}

    /**
     * @param User $user
     * @return Order
     */
    public function process(User $user): Order
    {
        $cartItems = $this->cartService->getCart($user);

        if ($cartItems->isEmpty()) {
            throw new \InvalidArgumentException('Cart is empty.');
        }

        $order = DB::transaction(function () use ($user, $cartItems) {
            // Validate stock for all items before processing
            foreach ($cartItems as $cartItem) {
                $this->stockService->validateAvailability(
                    $cartItem->product,
                    $cartItem->quantity
                );
            }

            // Calculate total
            $total = $cartItems->sum(fn ($item) => $item->quantity * $item->product->price);

            // Create order
            $order = $user->orders()->create([
                'total_amount' => $total,
                'status' => 'completed',
            ]);

            // Create order items and reduce stock
            foreach ($cartItems as $cartItem) {
                $order->orderItems()->create([
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                ]);

                $this->stockService->reduceStock($cartItem->product, $cartItem->quantity);
            }

            // Clear the cart
            $this->cartService->clear($user);

            return $order->load('orderItems.product');
        });

        return $order;
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getLowStockProducts(Order $order): array
    {
        $lowStockProducts = [];

        foreach ($order->orderItems as $orderItem) {
            $product = $orderItem->product->fresh();

            if ($this->stockService->isLowStock($product)) {
                $lowStockProducts[] = $product;
            }
        }

        return $lowStockProducts;
    }
}
