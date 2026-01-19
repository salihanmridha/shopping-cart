<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Jobs\LowStockNotificationJob;
use App\Services\StockService;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckLowStockListener implements ShouldQueue
{
    public function __construct() {}

    public function handle(OrderCompleted $event): void
    {
        $stockService = app(StockService::class);
        $lowStockProducts = [];

        // Refresh order to get order items (created after event was dispatched)
        $order = $event->order->fresh(['orderItems.product']);

        foreach ($order->orderItems as $orderItem) {
            $product = $orderItem->product;

            if ($product && $stockService->isLowStock($product)) {
                $lowStockProducts[] = $product;
            }
        }

        if (!empty($lowStockProducts)) {
            LowStockNotificationJob::dispatch($lowStockProducts);
        }
    }
}
