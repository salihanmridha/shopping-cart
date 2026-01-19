<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Order Confirmation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Success Message --}}
            @if(session('success'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 5000)"
                    class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center gap-3"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
        {{-- Order Header --}}
        <div class="bg-gray-50 p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Order #{{ $order->id }}</h2>
                    <p class="text-gray-600 mt-1">
                        Placed on {{ $order->created_at->format('F j, Y \a\t g:i A') }}
                    </p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
        </div>

        {{-- Order Items --}}
        <div class="divide-y divide-gray-200">
            @foreach($order->orderItems as $item)
                <div class="p-6 flex items-center justify-between">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ $item->product->name }}
                        </h3>
                        <p class="text-gray-600">
                            ${{ number_format($item->price, 2) }} × {{ $item->quantity }}
                        </p>
                    </div>
                    <div class="text-right font-medium text-gray-900">
                        ${{ number_format($item->quantity * $item->price, 2) }}
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Order Total --}}
        <div class="bg-gray-50 p-6 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <span class="text-lg font-medium text-gray-900">Total</span>
                <span class="text-2xl font-bold text-gray-900">
                    ${{ number_format($order->total_amount, 2) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Actions --}}
            <div class="mt-6 flex gap-4">
                <a
                    href="{{ route('products') }}"
                    wire:navigate
                    class="inline-block bg-blue-600 text-white py-3 px-6 rounded-md hover:bg-blue-700 transition-colors font-medium"
                >
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>
