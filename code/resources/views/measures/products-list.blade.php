@if($products->isEmpty())
    <div class="alert alert-danger">
        Non ci sono prodotti
    </div>
@else
    <ul>
        @foreach($products as $product)
            <li>{{ $product->printableName() }}</li>
        @endforeach
    </ul>
@endif
