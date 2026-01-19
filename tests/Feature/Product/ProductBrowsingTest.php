<?php

namespace Tests\Feature\Product;

use App\Livewire\ProductList;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_are_displayed_with_pagination(): void
    {
        Product::factory()->count(15)->create(['stock_quantity' => 10]);

        $component = Livewire::test(ProductList::class);

        // Access the computed property
        $products = $component->get('products');

        $this->assertEquals(12, $products->count()); // Default pagination
    }

    public function test_product_information_is_displayed_correctly(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
            'stock_quantity' => 5,
        ]);

        Livewire::test(ProductList::class)
            ->assertSee('Test Product');
    }

    public function test_authenticated_user_can_add_product_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        Livewire::actingAs($user)
            ->test(ProductList::class)
            ->call('addToCart', $product)
            ->assertDispatched('cart-updated');

        $this->assertEquals(1, $user->cartItems()->count());
    }

    public function test_unauthenticated_user_is_redirected_when_adding_to_cart(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        Livewire::test(ProductList::class)
            ->call('addToCart', $product)
            ->assertRedirect(route('login'));
    }

    public function test_cannot_add_out_of_stock_product_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 0]);

        Livewire::actingAs($user)
            ->test(ProductList::class)
            ->call('addToCart', $product)
            ->assertHasErrors('cart');
    }

    public function test_products_are_ordered_by_creation_date(): void
    {
        $product1 = Product::factory()->create(['created_at' => now()->subDays(2)]);
        $product2 = Product::factory()->create(['created_at' => now()->subDay()]);
        $product3 = Product::factory()->create(['created_at' => now()]);

        $component = Livewire::test(ProductList::class);
        $products = $component->get('products');

        $ids = $products->pluck('id')->toArray();

        $this->assertEquals($product3->id, $ids[0]);
        $this->assertEquals($product2->id, $ids[1]);
        $this->assertEquals($product1->id, $ids[2]);
    }

    public function test_cart_product_ids_are_tracked_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 10]);

        $user->cartItems()->create(['product_id' => $product1->id, 'quantity' => 2]);

        $component = Livewire::actingAs($user)->test(ProductList::class);
        $cartProductIds = $component->get('cartProductIds');

        $this->assertEquals([$product1->id], $cartProductIds);
    }

    public function test_cart_product_ids_are_empty_for_guest(): void
    {
        Product::factory()->count(3)->create(['stock_quantity' => 10]);

        $component = Livewire::test(ProductList::class);
        $cartProductIds = $component->get('cartProductIds');

        $this->assertEquals([], $cartProductIds);
    }
}
