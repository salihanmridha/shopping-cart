<x-mail::message>
# Daily Sales Report

**Report Date:** {{ $date->format('F j, Y') }}

## Summary

- **Total Orders:** {{ $orderCount }}
- **Total Items Sold:** {{ $totalQuantity }}
- **Total Revenue:** ${{ number_format($totalRevenue, 2) }}

@if (count($salesData) > 0)
## Sales Breakdown by Product

<x-mail::table>
| Product | Quantity Sold | Revenue |
|:--------|:-------------:|--------:|
@foreach ($salesData as $item)
| {{ $item['product_name'] }} | {{ $item['quantity_sold'] }} | ${{ number_format($item['revenue'], 2) }} |
@endforeach
</x-mail::table>
@else
## No Sales Today

There were no completed orders on this date.
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
