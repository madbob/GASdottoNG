{{ _i('Nome') }};{{ _i('Unità di Misura') }};{{ _i('Prezzo Unitario') }} (€);{{ _i('Trasporto') }} (€)
@foreach($supplier->products as $product)
"{{ $product->name }}";"{{ $product->measure->printableName() }}";{{ printablePrice($product->price, ',') }};{{ printablePrice($product->transport, ',') }}
@endforeach
