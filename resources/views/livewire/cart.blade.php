<div>
    {{-- Success Message --}}
    @if(session('success'))
        <div
            wire:key="success-{{ now()->timestamp }}"
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)"
            class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded"
        >
            {{ session('success') }}
        </div>
    @endif

    {{-- Error Messages --}}
    @error('cart')
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ $message }}
        </div>
    @enderror

    @if($this->cartItems->isEmpty())
        <div class="bg-white rounded-lg shadow-md py-20 px-8 text-center">
            <div class="flex items-center justify-center w-24 h-24 mx-auto bg-gray-100 rounded-full mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Your cart is empty</h3>
            <p class="text-gray-500 mb-8">Looks like you haven't added any products yet.</p>
            <a
                href="{{ route('products') }}"
                wire:navigate
                class="inline-block bg-blue-600 text-white py-3 px-10 rounded-md hover:bg-blue-700 transition-colors font-semibold text-base"
            >
                Browse Products
            </a>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            {{-- Cart Items --}}
            <div class="divide-y divide-gray-200">
                @foreach($this->cartItems as $item)
                    <div class="p-6 flex items-center justify-between" wire:key="cart-item-{{ $item->id }}">
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ $item->product->name }}
                            </h3>
                            <p class="text-gray-600">
                                ${{ number_format($item->product->price, 2) }} each
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            {{-- Quantity Controls --}}
                            <div class="flex items-center gap-2">
                                <button
                                    wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                    wire:loading.attr="disabled"
                                    @disabled($item->quantity <= 1)
                                    class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center
                                        {{ $item->quantity > 1 ? 'hover:bg-gray-100' : 'opacity-50 cursor-not-allowed' }}"
                                >
                                    -
                                </button>

                                <span class="w-12 text-center font-medium">
                                    {{ $item->quantity }}
                                </span>

                                <button
                                    wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                    wire:loading.attr="disabled"
                                    @disabled($item->quantity >= $item->product->stock_quantity)
                                    class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center
                                        {{ $item->quantity < $item->product->stock_quantity ? 'hover:bg-gray-100' : 'opacity-50 cursor-not-allowed' }}"
                                >
                                    +
                                </button>
                            </div>

                            {{-- Item Total --}}
                            <div class="w-24 text-right font-medium text-gray-900">
                                ${{ number_format($item->quantity * $item->product->price, 2) }}
                            </div>

                            {{-- Remove Button --}}
                            <button
                                wire:click="removeItem({{ $item->id }})"
                                wire:loading.attr="disabled"
                                wire:confirm="Remove this item from cart?"
                                class="text-red-600 hover:text-red-800 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Cart Summary --}}
            <div class="bg-gray-50 p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-lg font-medium text-gray-900">Total</span>
                    <span class="text-2xl font-bold text-gray-900">
                        ${{ number_format($this->cartTotal, 2) }}
                    </span>
                </div>

                <a
                    href="#"
                    {{-- TODO: href="{{ route('checkout') }}" when Task 10 is complete --}}
                    wire:navigate
                    class="block w-full bg-blue-600 text-white text-center py-3 px-4 rounded-md font-medium hover:bg-blue-700 transition-colors"
                >
                    Proceed to Checkout
                </a>
            </div>
        </div>
    @endif
</div>
