<?php

namespace Database\Seeders;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Regular stock products (15)
            ['name' => 'Wireless Bluetooth Headphones', 'price' => 79.99, 'stock_quantity' => 50],
            ['name' => 'USB-C Charging Cable', 'price' => 12.99, 'stock_quantity' => 100],
            ['name' => 'Laptop Stand Aluminum', 'price' => 45.00, 'stock_quantity' => 35],
            ['name' => 'Mechanical Keyboard RGB', 'price' => 129.99, 'stock_quantity' => 25],
            ['name' => 'Wireless Mouse Ergonomic', 'price' => 34.99, 'stock_quantity' => 60],
            ['name' => 'Monitor LED 27 Inch', 'price' => 299.00, 'stock_quantity' => 20],
            ['name' => 'Webcam HD 1080p', 'price' => 59.99, 'stock_quantity' => 40],
            ['name' => 'External SSD 500GB', 'price' => 89.99, 'stock_quantity' => 30],
            ['name' => 'Phone Case Protective', 'price' => 19.99, 'stock_quantity' => 80],
            ['name' => 'Desk Lamp LED', 'price' => 28.50, 'stock_quantity' => 45],
            ['name' => 'Portable Power Bank', 'price' => 39.99, 'stock_quantity' => 55],
            ['name' => 'HDMI Cable 2M', 'price' => 14.99, 'stock_quantity' => 90],
            ['name' => 'Notebook Leather Cover', 'price' => 24.99, 'stock_quantity' => 70],
            ['name' => 'Smart Watch Fitness', 'price' => 149.99, 'stock_quantity' => 22],
            ['name' => 'Bluetooth Speaker Mini', 'price' => 49.99, 'stock_quantity' => 38],

            // Low stock products (3) - for testing low stock notification
            ['name' => 'Gaming Controller Wireless', 'price' => 64.99, 'stock_quantity' => 5],
            ['name' => 'USB Hub 7-Port', 'price' => 32.99, 'stock_quantity' => 3],
            ['name' => 'Tablet Stand Adjustable', 'price' => 27.99, 'stock_quantity' => 4],

            // Out of stock products (2) - for testing add-to-cart validation
            ['name' => 'VR Headset Premium', 'price' => 399.99, 'stock_quantity' => 0],
            ['name' => 'Drone Camera 4K', 'price' => 599.99, 'stock_quantity' => 0],
        ];

        $baseTime = Carbon::now();

        foreach ($products as $index => $product) {
            Product::create([
                'name' => $product['name'],
                'price' => $product['price'],
                'stock_quantity' => $product['stock_quantity'],
                'created_at' => $baseTime->copy()->addSeconds($index),
                'updated_at' => $baseTime->copy()->addSeconds($index),
            ]);
        }
    }
}
