<x-larastrap::modal>
    <x-larastrap::iform method="POST" :action="route('products.updateprices', $product->id)">
        <input type="hidden" name="close-modal" value="1" class="skip-on-submit">

        <p>
            {!! _i('Ci sono ordini non ancora consegnati e archiviati in cui appare il prodotto di cui ha appena modificato il prezzo. Seleziona quelli in cui vuoi che venga applicato il nuovo prezzo (del prodotto e/o le differenze prezzi delle eventuali varianti).') !!}
        </p>
        <p>
            {!! _i("Se modifichi i prezzi e nell'ordine ci sono prenotazioni che sono già state consegnate, dovrai manualmente salvare nuovamente tali consegne affinché vengano rigenerati i nuovi movimenti contabili aggiornati.") !!}
        </p>

        <p>
            @foreach($orders as $order)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="orders[]" value="{{ $order->id }}" id="activate-order-{{ sanitizeId($order->id) }}">
                    <label class="form-check-label" for="activate-order-{{ sanitizeId($order->id) }}">
                        {{ $order->printableName() }}
                    </label>
                </div>
            @endforeach
        </p>
    </x-larastrap::iform>
</x-larastrap::modal>
