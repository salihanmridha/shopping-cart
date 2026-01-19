<x-mail::message>
# Low Stock Alert

The following products are running low on stock and require attention:

<x-mail::table>
| Product | Current Stock |
|:--------|:-------------:|
@foreach ($products as $product)
| {{ $product->name }} | {{ $product->stock_quantity }} |
@endforeach
</x-mail::table>

Please restock these items as soon as possible to avoid stockouts.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
