<?php

namespace Tests\Feature\Stock;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockValidationTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = app(StockService::class);
    }

    public function test_has_available_stock_returns_true_when_stock_is_sufficient(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->assertTrue($this->stockService->hasAvailableStock($product, 5));
        $this->assertTrue($this->stockService->hasAvailableStock($product, 10));
    }

    public function test_has_available_stock_returns_false_when_stock_is_insufficient(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $this->assertFalse($this->stockService->hasAvailableStock($product, 6));
        $this->assertFalse($this->stockService->hasAvailableStock($product, 100));
    }

    public function test_has_available_stock_returns_false_for_zero_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 0]);

        $this->assertFalse($this->stockService->hasAvailableStock($product, 1));
    }

    public function test_validate_availability_passes_when_stock_is_sufficient(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        // Should not throw exception
        $this->stockService->validateAvailability($product, 5);
        $this->assertTrue(true); // If we reach here, validation passed
    }

    public function test_validate_availability_throws_exception_when_stock_is_insufficient(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 3,
            'name' => 'Test Product',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient stock for Test Product. Available: 3');

        $this->stockService->validateAvailability($product, 5);
    }

    public function test_validate_availability_throws_exception_for_zero_stock(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 0,
            'name' => 'Out of Stock Product',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient stock for Out of Stock Product. Available: 0');

        $this->stockService->validateAvailability($product, 1);
    }

    public function test_reduce_stock_decrements_quantity_correctly(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->stockService->reduceStock($product, 3);

        $this->assertEquals(7, $product->fresh()->stock_quantity);
    }

    public function test_reduce_stock_can_reduce_to_zero(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $this->stockService->reduceStock($product, 5);

        $this->assertEquals(0, $product->fresh()->stock_quantity);
    }

    public function test_reduce_stock_multiple_times(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 20]);

        $this->stockService->reduceStock($product, 5);
        $this->assertEquals(15, $product->fresh()->stock_quantity);

        $this->stockService->reduceStock($product, 3);
        $this->assertEquals(12, $product->fresh()->stock_quantity);

        $this->stockService->reduceStock($product, 7);
        $this->assertEquals(5, $product->fresh()->stock_quantity);
    }

    public function test_is_low_stock_returns_true_when_below_threshold(): void
    {
        $threshold = $this->stockService->getLowStockThreshold();
        $product = Product::factory()->create(['stock_quantity' => $threshold - 1]);

        $this->assertTrue($this->stockService->isLowStock($product));
    }

    public function test_is_low_stock_returns_true_when_at_threshold(): void
    {
        $threshold = $this->stockService->getLowStockThreshold();
        $product = Product::factory()->create(['stock_quantity' => $threshold]);

        $this->assertTrue($this->stockService->isLowStock($product));
    }

    public function test_is_low_stock_returns_false_when_above_threshold(): void
    {
        $threshold = $this->stockService->getLowStockThreshold();
        $product = Product::factory()->create(['stock_quantity' => $threshold + 1]);

        $this->assertFalse($this->stockService->isLowStock($product));
    }

    public function test_is_low_stock_returns_true_for_zero_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 0]);

        $this->assertTrue($this->stockService->isLowStock($product));
    }

    public function test_low_stock_threshold_is_configurable(): void
    {
        $threshold = $this->stockService->getLowStockThreshold();

        $this->assertIsInt($threshold);
        $this->assertGreaterThanOrEqual(0, $threshold);
    }
}
