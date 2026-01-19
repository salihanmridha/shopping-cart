<x-mail::message>
# Thank You for Your Order!

Hi {{ $order->user->name }},

Your order has been successfully placed and is being processed.

**Order ID:** #{{ $order->id }}
**Date:** {{ $order->created_at->format('F j, Y g:i A') }}

## Order Summary

<x-mail::table>
| Product | Quantity | Price | Subtotal |
|:--------|:--------:|------:|---------:|
@foreach ($order->orderItems as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | ${{ number_format($item->price, 2) }} | ${{ number_format($item->price * $item->quantity, 2) }} |
@endforeach
| **Total** | | | **${{ number_format($order->total_amount, 2) }}** |
</x-mail::table>

<x-mail::button :url="url('/products')">
Continue Shopping
</x-mail::button>

Thanks for shopping with us!<br>
{{ config('app.name') }}
</x-mail::message>
