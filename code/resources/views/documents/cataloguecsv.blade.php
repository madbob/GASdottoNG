Nome;Unità di Misura;Prezzo Unitario (€);Trasporto (€)
@foreach($supplier->products as $product)
"{{ $product->name }}";"{{ $product->measure->printableName() }}";{{ printablePrice($product->price) }};{{ printablePrice($product->transport) }}
@endforeach
