<form method="POST" action="{{ url('orders/fixes/' . $order->id) }}">
    <input type="hidden" name="product" value="{{ $product->id }}" />

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">{{ _i('Modifica Quantità') }}</h4>
    </div>
    <div class="modal-body">
        @if($product->package_size != 0)
            <p>
                {{ _i('Dimensione Confezione') }}: {{ $product->package_size }}
            </p>

            <hr/>
        @endif

        @if($order->bookings->isEmpty())
            <div class="alert alert-info">{{ _i("Da qui è possibile modificare la quantità prenotata di questo prodotto per ogni prenotazione, ma nessun utente ha ancora partecipato all'ordine.") }}</div>
        @else
            <table class="table table-striped">
                @foreach($order->bookings as $po)
                    <tr>
                        <td>
                            <label>{{ $po->user->printableName() }}</label>
                        </td>
                        <td>
                            <input type="hidden" name="booking[]" value="{{ $po->id }}" />

                            <div class="input-group">
                                <input type="text" class="form-control number" name="quantity[]" value="{{ $po->getBookedQuantity($product) }}" />
                                <div class="input-group-addon">{{ $product->printableMeasure() }}</div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
        <button type="submit" class="btn btn-primary reloader" data-reload-target="#order-list">{{ _i('Salva') }}</button>
    </div>
</form>
