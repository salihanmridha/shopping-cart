<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create regular stock products
        Product::factory(10)->create();

        // Create low stock products (for testing low stock notification)
        Product::factory(3)->lowStock()->create();

        // Create out of stock products (for testing add-to-cart validation)
        Product::factory(2)->outOfStock()->create();
    }
}
