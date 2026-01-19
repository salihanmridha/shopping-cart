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

    {{-- Product Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($this->products as $product)
            <div class="bg-white rounded-lg shadow-md overflow-hidden" wire:key="product-{{ $product->id }}">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        {{ $product->name }}
                    </h3>

                    <p class="text-2xl font-bold text-gray-900 mb-2">
                        ${{ number_format($product->price, 2) }}
                    </p>

                    <p class="text-sm mb-4 {{ $product->stock_quantity > 0 ? 'text-gray-600' : 'text-red-600' }}">
                        @if($product->stock_quantity > 0)
                            {{ $product->stock_quantity }} in stock
                        @else
                            Out of stock
                        @endif
                    </p>

                    <button
                        wire:click="addToCart({{ $product->id }})"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        @disabled($product->stock_quantity === 0)
                        class="w-full py-2 px-4 rounded-md font-medium transition-colors
                            {{ $product->stock_quantity > 0
                                ? 'bg-blue-600 text-white hover:bg-blue-700'
                                : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                    >
                        <span wire:loading.remove wire:target="addToCart({{ $product->id }})">
                            {{ $product->stock_quantity > 0 ? 'Add to Cart' : 'Out of Stock' }}
                        </span>
                        <span wire:loading wire:target="addToCart({{ $product->id }})">
                            Adding...
                        </span>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                No products available.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $this->products->links() }}
    </div>
</div>
