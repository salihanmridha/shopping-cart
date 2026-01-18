<?php

namespace App\Services;

use App\Models\Product;

class StockService
{

    /**
     * @param Product $product
     * @param int $quantity
     * @return bool
     */
    public function hasAvailableStock(Product $product, int $quantity): bool
    {
        return $product->stock_quantity >= $quantity;
    }

    /**
     * @param Product $product
     * @param int $quantity
     * @return void
     */
    public function validateAvailability(Product $product, int $quantity): void
    {
        if (!$this->hasAvailableStock($product, $quantity)) {
            throw new \InvalidArgumentException(
                "Insufficient stock for {$product->name}. Available: {$product->stock_quantity}"
            );
        }
    }

    /**
     * @param Product $product
     * @param int $quantity
     * @return void
     */
    public function reduceStock(Product $product, int $quantity): void
    {
        $product->decrement('stock_quantity', $quantity);
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function isLowStock(Product $product): bool
    {
        return $product->stock_quantity <= $this->getLowStockThreshold();
    }

    /**
     * @return int
     */
    public function getLowStockThreshold(): int
    {
        return (int) config('shop.low_stock_threshold', 5);
    }
}
