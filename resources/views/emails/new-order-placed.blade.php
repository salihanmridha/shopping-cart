<x-mail::message>
# New Order Received

A new order has been placed on your store.

**Order ID:** #{{ $order->id }}
**Customer:** {{ $order->user->name }}
**Email:** {{ $order->user->email }}
**Date:** {{ $order->created_at->format('F j, Y g:i A') }}

## Order Details

<x-mail::table>
| Product | Quantity | Price | Subtotal |
|:--------|:--------:|------:|---------:|
@foreach ($order->orderItems as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | ${{ number_format($item->price, 2) }} | ${{ number_format($item->price * $item->quantity, 2) }} |
@endforeach
| **Total** | | | **${{ number_format($order->total_amount, 2) }}** |
</x-mail::table>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
