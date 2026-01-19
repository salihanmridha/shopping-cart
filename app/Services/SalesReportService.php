<?php

namespace App\Services;

use App\Models\OrderItem;
use Carbon\Carbon;

class SalesReportService
{
    /**
     * Get aggregated sales data for a specific date.
     *
     * @param Carbon $date
     * @return array
     */
    public function getDailySalesData(Carbon $date): array
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        $orderItems = $this->getCompletedOrderItems($startOfDay, $endOfDay);

        return [
            'items' => $this->aggregateByProduct($orderItems),
            'total_revenue' => $this->calculateTotalRevenue($orderItems),
            'total_quantity' => $orderItems->sum('quantity'),
            'order_count' => $orderItems->pluck('order_id')->unique()->count(),
        ];
    }

    /**
     * Get completed order items within date range.
     *
     * Note: For large datasets, consider using SQL-level aggregation with
     * JOIN and GROUP BY instead of Eloquent collection processing.
     *
     * @param Carbon $startOfDay
     * @param Carbon $endOfDay
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getCompletedOrderItems(Carbon $startOfDay, Carbon $endOfDay)
    {
        return OrderItem::with('product')
            ->whereHas('order', function ($query) use ($startOfDay, $endOfDay) {
                $query->whereBetween('created_at', [$startOfDay, $endOfDay])
                    ->where('status', 'completed');
            })
            ->get();
    }

    /**
     * Aggregate order items by product.
     *
     * @param \Illuminate\Database\Eloquent\Collection $orderItems
     * @return array
     */
    private function aggregateByProduct($orderItems): array
    {
        return $orderItems->groupBy('product_id')->map(function ($items) {
            $firstItem = $items->first();

            return [
                'product_name' => $firstItem->product->name ?? 'Unknown Product',
                'quantity_sold' => $items->sum('quantity'),
                'revenue' => $this->calculateTotalRevenue($items),
            ];
        })->sortByDesc('revenue')->values()->all();
    }

    /**
     * Calculate total revenue from order items.
     *
     * @param \Illuminate\Support\Collection $orderItems
     * @return float
     */
    private function calculateTotalRevenue($orderItems): float
    {
        return $orderItems->sum(fn ($item) => $item->quantity * $item->price);
    }
}
