<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    #[Computed]
    public function cartProductIds(): array
    {
        if (!Auth::check()) {
            return [];
        }

        return Auth::user()->cartItems()->pluck('product_id')->toArray();
    }

    public function addToCart(Product $product, CartService $cartService): void
    {
        if (!Auth::check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        try {
            $cartService->add(Auth::user(), $product);
            unset($this->cartProductIds);
            $this->dispatch('cart-updated');
        } catch (\InvalidArgumentException $e) {
            $this->addError('cart', $e->getMessage());
        }
    }

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        return Product::orderBy('created_at', 'desc')->paginate(12);
    }

    public function render(): View
    {
        return view('livewire.product-list');
    }
}
