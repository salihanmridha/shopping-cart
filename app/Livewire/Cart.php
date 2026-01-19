<?php

namespace App\Livewire;

use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Cart extends Component
{
    #[On('cart-updated')]
    public function refreshCart(): void
    {
        unset($this->cartItems);
        unset($this->cartTotal);
    }

    public function updateQuantity(CartItem $cartItem, int $quantity, CartService $cartService): void
    {
        try {
            $cartService->update(Auth::user(), $cartItem, $quantity);
            session()->flash('success', 'Cart updated.');
            $this->refreshCart();
        } catch (\InvalidArgumentException $e) {
            $this->addError('cart', $e->getMessage());
        }
    }

    public function removeItem(CartItem $cartItem, CartService $cartService): void
    {
        try {
            $cartService->remove(Auth::user(), $cartItem);
            session()->flash('success', 'Item removed from cart.');
            $this->refreshCart();
        } catch (\InvalidArgumentException $e) {
            $this->addError('cart', $e->getMessage());
        }
    }

    #[Computed]
    public function cartItems(): Collection
    {
        return app(CartService::class)->getCart(Auth::user());
    }

    #[Computed]
    public function cartTotal(): float
    {
        return app(CartService::class)->getCartTotal(Auth::user());
    }

    public function render(): View
    {
        return view('livewire.cart');
    }
}
