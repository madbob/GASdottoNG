<x-larastrap::modal id="delete-confirm-modal" :title="_i('Elimina')" size="lg">
    <x-larastrap::iform method="DELETE" :action="route('products.destroy', $product->id)" id="form-delete-confirm-modal" :buttons="[['type' => 'submit', 'color' => 'danger', 'label' => _i('Conferma')]]">
        <input type="hidden" name="close-modal" value="1">
        @include('commons.extrafields', [
            'extra' => [
                'post-saved-function' => ['removeTargetListItem'],
            ],
        ])

        @php

        $orders = App\Order::whereHas('products', function($query) use ($product) {
            $query->where('order_product.product_id', $product->id);
        })->whereIn('status', ['open', 'closed'])->get();

        @endphp

        @if($orders->count() > 0)
            {{ _i('Il prodotto è attualmente incluso in ordini non ancora consegnati. Cosa vuoi fare?') }}

            <hr>

            @foreach($orders as $order)
                <x-larastrap::radiolist :name="sprintf('order_%s', inlineId($order))" :label="$order->printableName()" :options="['keep' => _i('Lascia il prodotto'), 'leave' => _i('Togli il prodotto ed elimina tutte le relative prenotazioni')]" value="keep" />
            @endforeach
        @else
            {!! _i('Vuoi davvero eliminare il prodotto "%s"?', [$product->printableName()]) !!}
        @endif
    </x-larastrap::iform>
</x-larastrap::modal>
