<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CartService
{
    /**
     * @param StockService $stockService
     */
    public function __construct(
        private StockService $stockService
    ) {}


    /**
     * @param User $user
     * @return Collection
     */
    public function getCart(User $user): Collection
    {
        return $user->cartItems()->with('product')->get();
    }


    /**
     * @param User $user
     * @return float
     */
    public function getCartTotal(User $user): float
    {
        return (float) $user->cartItems()
            ->with('product')
            ->get()
            ->sum(fn (CartItem $item) => $item->quantity * $item->product->price);
    }


    /**
     * @param User $user
     * @param Product $product
     * @param int $quantity
     * @return CartItem
     */
    public function add(User $user, Product $product, int $quantity = 1): CartItem
    {
        $this->validateQuantity($quantity);

        $existingItem = $user->cartItems()
            ->where('product_id', $product->id)
            ->first();

        $newQuantity = $existingItem ? $existingItem->quantity + $quantity : $quantity;

        $this->stockService->validateAvailability($product, $newQuantity);

        if ($existingItem) {
            $existingItem->update(['quantity' => $newQuantity]);
            return $existingItem->fresh();
        }

        return $user->cartItems()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
        ]);
    }


    /**
     * @param User $user
     * @param CartItem $cartItem
     * @param int $quantity
     * @return CartItem
     */
    public function update(User $user, CartItem $cartItem, int $quantity): CartItem
    {
        $this->authorizeCartItem($user, $cartItem);
        $this->validateQuantity($quantity);
        $this->stockService->validateAvailability($cartItem->product, $quantity);

        $cartItem->update(['quantity' => $quantity]);

        return $cartItem->fresh();
    }


    /**
     * @param User $user
     * @param CartItem $cartItem
     * @return bool
     */
    public function remove(User $user, CartItem $cartItem): bool
    {
        $this->authorizeCartItem($user, $cartItem);

        return $cartItem->delete();
    }


    /**
     * @param User $user
     * @return void
     */
    public function clear(User $user): void
    {
        $user->cartItems()->delete();
    }


    /**
     * @param int $quantity
     * @return void
     */
    private function validateQuantity(int $quantity): void
    {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('Quantity must be at least 1.');
        }
    }


    /**
     * @param User $user
     * @param CartItem $cartItem
     * @return void
     */
    private function authorizeCartItem(User $user, CartItem $cartItem): void
    {
        if ($cartItem->user_id !== $user->id) {
            throw new \InvalidArgumentException('Cart item does not belong to this user.');
        }
    }
}
